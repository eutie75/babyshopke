export type AuthUser = {
  fullName: string;
  email: string;
};

const AUTH_USER_KEY = "babyshopke_auth_user";

export const getStoredAuthUser = (): AuthUser | null => {
  if (typeof window === "undefined") {
    return null;
  }

  try {
    const rawUser = window.localStorage.getItem(AUTH_USER_KEY);
    if (!rawUser) {
      return null;
    }

    const parsedUser = JSON.parse(rawUser) as Partial<AuthUser>;
    if (!parsedUser.fullName || !parsedUser.email) {
      return null;
    }

    return {
      fullName: parsedUser.fullName,
      email: parsedUser.email,
    };
  } catch {
    return null;
  }
};

export const setStoredAuthUser = (user: AuthUser): void => {
  if (typeof window === "undefined") {
    return;
  }

  window.localStorage.setItem(AUTH_USER_KEY, JSON.stringify(user));
};

export const clearStoredAuthUser = (): void => {
  if (typeof window === "undefined") {
    return;
  }

  window.localStorage.removeItem(AUTH_USER_KEY);
};
