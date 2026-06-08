import { defineStore } from 'pinia';

const PUBLIC_ID_STORAGE_KEY = 'totem-animal.public_id';

interface UserState {
  publicId: string | null;
  hasHydrated: boolean;
}

function readPublicId(): string | null {
  if (typeof window === 'undefined') {
    return null;
  }

  const value = window.localStorage.getItem(PUBLIC_ID_STORAGE_KEY);
  return value && value.trim() ? value : null;
}

export const useUserStore = defineStore('user', {
  state: (): UserState => ({
    publicId: null,
    hasHydrated: false,
  }),
  actions: {
    hydrateFromStorage() {
      this.publicId = readPublicId();
      this.hasHydrated = true;
    },
    setPublicId(publicId: string | null) {
      this.publicId = publicId;

      if (typeof window === 'undefined') {
        return;
      }

      if (publicId) {
        window.localStorage.setItem(PUBLIC_ID_STORAGE_KEY, publicId);
      } else {
        window.localStorage.removeItem(PUBLIC_ID_STORAGE_KEY);
      }
    },
    clearPublicId() {
      this.setPublicId(null);
    },
  },
});
