# supportingmarriage.com – custom code

All custom code that used to live in the child theme's `functions.php` now lives in a plugin.

```
supportingmarriage-custom/          <- the plugin (upload to wp-content/plugins/)
  supportingmarriage-custom.php     <- loader + safety guard
  includes/custom-functions.php     <- the code, moved verbatim from functions.php
child-theme/functions.php           <- new slim functions.php (only the child stylesheet enqueue)
backup/functions-original.php       <- the original functions.php, for rollback
```

## How the switch avoids downtime

If the same function exists in both the theme and a plugin, PHP stops with a
"Cannot redeclare function" fatal error. To prevent that, the plugin loads its code on
`after_setup_theme` (priority 0), which runs right after the theme's `functions.php`, so the timing
is the same as before. It checks first: if the old code is still in the theme, the plugin
stands by and shows an admin notice. As soon as `functions.php` is slimmed down, the plugin
takes over on the next request.

Activate the plugin first, then replace `functions.php`. There is never a moment with the code
loaded twice or not loaded at all.

## Deploy steps (live site)

1. **Backup**: take a SiteGround backup (Site Tools → Security → Backups → Create), and download the
   current `wp-content/themes/<child-theme>/functions.php` via File Manager/SFTP.
2. **Install plugin**: zip the `supportingmarriage-custom` folder and upload it in Plugins → Add New → Upload
   (or upload the folder to `wp-content/plugins/` by SFTP). **Activate** it.
   You should see the yellow "standing by" notice. The site behaves exactly as before.
3. **Swap functions.php**: in File Manager/SFTP, replace the child theme's `functions.php` with
   `child-theme/functions.php` from this repo. Avoid the WP admin Theme File Editor for this step.
   Use SFTP or File Manager so you can revert if the editor fails.
4. **Check**: the admin notice disappears. Purge the SG Optimizer cache, then test:
   - My Account tabs (Basic Information … Additional Information), saving + photo upload
   - Users list "Member ID" column, Users → Registration Statistics
   - A Forminator registration / psychometric form submission
   - Login/logout from header and footer, shop page video, product reviews tab
   - A secure member file link (photo/document) as admin

## Rollback

- Put `backup/functions-original.php` back as the child theme's `functions.php` → the plugin
  automatically goes back to standing by. Then deactivate it if you like.
- If wp-admin is unreachable: rename `wp-content/plugins/supportingmarriage-custom` via SFTP and
  restore the original `functions.php`.

## Notes

- Don't edit the code in both places. From now on, edit `includes/custom-functions.php` only.
- The code was moved as-is (no refactoring), so behaviour is identical. Splitting it into
  smaller files by feature can be done later as a separate, tested change.
