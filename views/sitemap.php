<?php

header('Content-type: application/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($pageManager->getCategories() as $cat) {
    foreach ($cat->getPages() as $page) {
        if ($page->isSitemap()) {
            ?>
            <url>
                <loc><?php echo $config['url']['protocol'] . $config['url']['domain'] . $page->getLink(); ?></loc>
                <priority>1.0</priority>
                <changefreq>always</changefreq>
            </url>
        <?php
        }
    }
}
echo '</urlset>';