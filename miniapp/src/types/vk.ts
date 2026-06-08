import type { AppearanceType } from '@vkontakte/vk-bridge';

export interface VkLaunchParams {
  vk_app_id?: string;
  vk_are_notifications_enabled?: string;
  vk_is_app_user?: string;
  vk_language?: string;
  vk_platform?: string;
  vk_ref?: string;
  vk_ts?: string;
  vk_viewer_group_role?: string;
  vk_user_id?: string;
  sign?: string;
  [key: string]: string | undefined;
}

export interface VkViewSettings {
  status_bar_style: AppearanceType;
  action_bar_color?: string;
  navigation_bar_color?: string;
}
