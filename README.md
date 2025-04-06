# SEO-test

## SEO Implementation
- Virtual Pages: Pages are generated on-the-fly without database entries

- Proper Status Codes: Returns 200 for valid profiles, 404 for non-existent ones

- Sitemap: Custom sitemap feed at /sitemap-people.xml with all profile URLs

- Indexable: Clean HTML output with proper meta tags (via WordPress header)

## Bonus Features
- Caching: GitHub API responses are cached for 24 hours

- Error Handling: Graceful degradation when GitHub data is unavailable

- Clean URLs: SEO-friendly URLs with proper rewrite rules

## Usage
- Install and activate the SEO People Directory plugin

- Install and activate the SEO People Directory Theme

- Use seo-test.sql database in the root directory

- Add profiles to the CSV file (\wp-content\plugins\seo-test\assets\profiles.csv)

- The sitemap will be available at /sitemap-people.xml

- Profile pages will be accessible at /people/{slug}/

- Credentials for /wp-admin admin/admin