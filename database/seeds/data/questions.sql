INSERT INTO questions (
    `code`,
    `text`,
    `is_active`,
    `sort_order`,
    `created_at`,
    `updated_at`,
    `deleted_at`
) VALUES (
             :code,
             :text,
             :is_active,
             :sort_order,
             :created_at,
             :updated_at,
             :deleted_at
         )
    ON DUPLICATE KEY UPDATE
                         `text` = VALUES(`text`),
                         `is_active` = VALUES(`is_active`),
                         `sort_order` = VALUES(`sort_order`),
                         `updated_at` = VALUES(`updated_at`),
                         `deleted_at` = VALUES(`deleted_at`)