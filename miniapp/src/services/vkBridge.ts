import bridge from '@vkontakte/vk-bridge';

import type { VkLaunchParams, VkViewSettings } from '@/types/vk';

let isInitialized = false;

export function initVkBridge(): Promise<boolean> {
  if (isInitialized) {
    return Promise.resolve(true);
  }

  if (typeof window === 'undefined') {
    return Promise.resolve(false);
  }

  return bridge
    .send('VKWebAppInit')
    .then(() => {
      isInitialized = true;
      return true;
    })
    .catch(() => false);
}

export function getVkLaunchParams(): VkLaunchParams {
  if (typeof window === 'undefined') {
    return {};
  }

  const params = new URLSearchParams(window.location.search);
  const launchParams: VkLaunchParams = {};

  for (const [key, value] of params.entries()) {
    launchParams[key] = value;
  }

  return launchParams;
}

export function setViewSettings(settings: VkViewSettings): Promise<unknown> {
  if (typeof window === 'undefined') {
    return Promise.resolve();
  }

  return bridge.send('VKWebAppSetViewSettings', settings);
}
