# pikozm-website (Laravel)

The official Piko Technology Services website, pikozm.com, rebuilt as a Laravel
application. It replaces the static `.html` pages and the stand-alone `payments/`
PHP module with one app that has controllers, models, routes, Blade layouts and a database.

## What the site does

| Area | URL | What happens |
|---|---|---|
| Home | `/` | Company intro, services, partner logos. The "Let's Connect" form is saved, then opened in WhatsApp |
| Payments (DPO Pay) | `/payments` | Catalog of subscriptions and one-off services, plus "Paying for something else?". Checkout creates an `Order`, gets a DPO token and redirects to DPO. DPO sends the customer back to `/payments/success` or `/payments/cancel` |
| PikoPay (Lenco) | `/pikopay` | Free-form client payments through the Lenco inline widget |
| Digital Impact (PDIP) | `/digital-impact` | Donations (Lenco), partnership requests, volunteer sign-ups with ID and CV upload, NGO support requests |
| Talent | `/talent` | "Join Our Team" registration, saved and then opened in WhatsApp |
| Promo | `/promo` | Business Booster packages and registration, saved and then opened in WhatsApp |

Old URLs such as `index.html`, `digital-impact.html`, `register.html`, `payments.html`,
`payments/index.php` and `payments/payment-success.php` redirect with **301** to the new
routes, and the query string is kept. Shared links and the DPO callback URLs keep working.

## Where things live (old → new)

| Before | Now |
|---|---|
| `index.html`, `talent.html`, `register.html` | `resources/views/pages/*.blade.php` + `HomeController`, `TalentController`, `PromoController` |
| `digital-impact.html` (+ inline JS → app.pikozm.com API) | `resources/views/digital-impact/` + `App\Http\Controllers\DigitalImpact\*` + `public/js/pages/digital-impact.js` |
| `payments.html` (→ app.pikozm.com API) | `resources/views/pikopay/index.blade.php` + `PikoPayController` |
| `payments/index.php` | `Payments\CatalogController` + `resources/views/payments/index.blade.php` |
| `payments/checkout.php`, `process-payment.php` | `Payments\CheckoutController` |
| `payments/payment-success.php`, `payment-cancel.php` | `Payments\DpoCallbackController` |
| `payments/includes/dpo.php` | `App\Services\DpoPay` |
| `payments/includes/config.php` | `config/dpo.php` + `.env` |
| `payments/includes/products.php` | `products` table, seeded by `ProductSeeder` (`App\Models\Product`) |
| `payments/data/orders.json` | `orders` table (`App\Models\Order`) |
| Navbar and footer copied into every page | `resources/views/layouts/app.blade.php`, `partials/navbar`, `partials/footer` |
| Phone numbers, emails, social links in every file | `config/piko.php` |
| `css/ js/ fonts/ images/ videos/` | `public/…` (same paths) |
| `*.pdf` legal docs | `public/docs/` |

### Models

`Product`, `Order` (DPO) · `ClientPayment`, `Donation` (Lenco) · `VolunteerApplication`,
`PartnershipRequest`, `SupportRequest` (PDIP) · `ContactMessage`, `TalentApplication`,
`PromoPackage`, `PromoRegistration` (leads).

## Run it locally (XAMPP on macOS)

You need **PHP 8.2 or newer** and Composer. Check XAMPP's PHP version:

```bash
/Applications/XAMPP/xamppfiles/bin/php -v
```

On PHP 8.3 or newer, Composer installs Laravel 13. On PHP 8.2 it installs Laravel 12. The code works on both.
If XAMPP's PHP is older than 8.2, use Homebrew PHP instead (`brew install php composer`).

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/pikozm-website/pikozm-laravel
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite          # or set DB_* in .env to use XAMPP MySQL
php artisan migrate --seed
php artisan piko:import-orders          # optional: imports ../payments/data/orders.json
php artisan serve                       # http://127.0.0.1:8000
```

To use XAMPP's MySQL instead of SQLite, create a database in phpMyAdmin and set these values in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pikozm
DB_USERNAME=root
DB_PASSWORD=
```

## Configuration (.env)

| Key | Purpose |
|---|---|
| `DPO_COMPANY_TOKEN` | Currently DPO's **test** token. Replace it with the live token once DPO approves the account |
| `DPO_REDIRECT_URL` / `DPO_BACK_URL` | Leave blank. The app sends DPO its own `/payments/success` and `/payments/cancel` URLs |
| `DPO_DEFAULT_SERVICE_TYPE` | Service type for free-form payments (85325 = DPO test service) |
| `LENCO_PUBLIC_KEY` / `LENCO_SECRET_KEY` | Required for PikoPay and donations. Without them those forms return "not available right now" |

Product prices and DPO service type codes are in `database/seeders/ProductSeeder.php`.
Re-running `php artisan db:seed --class=ProductSeeder` updates rows in place, matched by slug.

In the Lenco dashboard, point the webhook at `https://pikozm.com/webhooks/lenco`.
The request signature is checked (HMAC-SHA512).

## Deploying to cPanel shared hosting

1. Upload the project **outside** `public_html`, for example to `~/pikozm-laravel`.
2. Make `public_html` serve `pikozm-laravel/public`. Either change the domain's document root,
   or copy the contents of `public/` into `public_html` and edit the two paths in `public_html/index.php`
   so they point to `../pikozm-laravel/vendor/autoload.php` and `../pikozm-laravel/bootstrap/app.php`.
3. **Remove the old static files and the old `payments/` folder** from `public_html`.
   Apache serves real files and folders before Laravel's routes, so a leftover `payments/` folder
   would hide the new `/payments` page.
4. Set `APP_ENV=production`, `APP_DEBUG=false` and `APP_URL=https://pikozm.com` in `.env`, then run:
   `php artisan migrate --force && php artisan db:seed --force && php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. Make `storage/` and `bootstrap/cache/` writable by the web server.

## Tests

```bash
php artisan test
```

The tests cover every page and the legacy redirects. They also cover the WhatsApp lead forms;
the DPO checkout (with the DPO API faked) for token creation, the price-tampering guard, XML escaping,
and the paid, unpaid and cancelled callbacks; Lenco initiation and verification; webhook signature
checks; and volunteer file uploads.

## Behaviour changes worth knowing

- **Lead forms are now stored.** The contact, talent and promo forms still open WhatsApp
  with the same pre-filled message, and they also save a copy to the database first.
- **PDIP and PikoPay no longer call `app.pikozm.com`.** Their endpoints now run inside this app.
  Volunteer ID documents and CVs are stored privately in `storage/app/private/volunteers`.
- **Fixed prices are enforced on the server.** For catalog items, any amount posted from the browser is ignored.
- **Form rate limiting:** each visitor can submit forms at most 10 times a minute.
- **Broken legal links are fixed.** Some pages linked to `privacy-policy.pdf` (hyphenated), a file that did not exist.
  Every page now uses the files in `public/docs/`.
