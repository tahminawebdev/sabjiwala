Groser Child Theme
===================

This is a minimal child theme for the Groser parent theme.

Install & Activate
- Upload the `groser-child` folder to wp-content/themes/ (or keep it if this package was deployed directly).
- In WordPress Admin, go to Appearance → Themes and Activate “Groser Child”.

What it does
- Inherits all templates and functionality from the Groser parent theme.
- Enqueues the parent stylesheet automatically and then the child stylesheet.

Customize
- Add custom CSS in Appearance → Customize → Additional CSS or in groser-child/style.css.
- Add custom PHP (hooks/filters/functions) in groser-child/functions.php.

Notes
- Keep the parent theme (groser) installed. The child theme requires it.
- Update the parent theme as usual; your child modifications will remain intact.
