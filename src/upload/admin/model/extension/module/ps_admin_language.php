<?php
class ModelExtensionModulePsProductCategoryFilter extends Model
{
    public function install()
    {
        $query = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "category_filter` WHERE `Key_name` = 'category_filter_unique'");

        if (!$query->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "category_filter` ADD UNIQUE `category_filter_unique` (`category_id`, `filter_id`)");
        }
    }

    public function uninstall()
    {
        $query = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . "category_filter` WHERE `Key_name` = 'category_filter_unique'");

        if ($query->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "category_filter` DROP INDEX `category_filter_unique`");
        }
    }

    public function removeUnusedFilters($category_id)
    {
        $category_filter_query = $this->db->query("SELECT `filter_id` FROM `" . DB_PREFIX . "category_filter` WHERE `category_id` = '" . (int) $category_id . "'");

        $category_filter_ids = array_column($category_filter_query->rows, 'filter_id');

        if ($category_filter_ids) {
            $product_filter_query = $this->db->query("SELECT DISTINCT pf.`filter_id` FROM `" . DB_PREFIX . "product_to_category` p2c
                INNER JOIN `" . DB_PREFIX . "product_filter` pf ON pf.`product_id` = p2c.`product_id` AND pf.`filter_id` IN (" . implode(",", $category_filter_ids) . ")
                WHERE p2c.`category_id` = '" . (int) $category_id . "'");

            $product_filter_ids = array_column($product_filter_query->rows, 'filter_id');

            $delete_filter_id = array_diff($category_filter_ids, $product_filter_ids);

            if ($delete_filter_id) {
                $this->db->query("DELETE FROM `" . DB_PREFIX . "category_filter` WHERE `category_id` = '" . (int) $category_id . "' AND `filter_id` IN (" . implode(",", $delete_filter_id) . ")");
            }
        }
    }
}
