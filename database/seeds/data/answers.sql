INSERT INTO answers (
    `question_id`,
    `code`,
    `text`,
    `weights`,
    `sort_order`,
    `is_active`,
    `created_at`,
    `updated_at`,
    `deleted_at`
) VALUES (
             :question_id,
             :code,
             :text,
             :weights,
             :sort_order,
             :is_active,
             :created_at,
             :updated_at,
             :deleted_at
         )
    ON DUPLICATE KEY UPDATE
                         `text` = VALUES(`text`),
                         `weights` = VALUES(`weights`),
                         `sort_order` = VALUES(`sort_order`),
                         `is_active` = VALUES(`is_active`),
                         `updated_at` = VALUES(`updated_at`),
                         `deleted_at` = VALUES(`deleted_at`)