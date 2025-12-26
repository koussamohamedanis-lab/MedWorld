
import { AuthAPI } from '$lib/api';
import { writable } from 'svelte/store';
import type { User } from '$lib/types/users';

interface AuthState {
    isLoading: boolean;
    error: string | null;
    user: User<any> | null;
    isAuthenticated: boolean;
}

function createAuthStore() {
    const { subscribe, set, update } = writable<AuthState>({
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: null
    });

    return {
        subscribe,

        async login(email: string, password: string) {
            update(state => ({ ...state, isLoading: true, error: null }));

            try {
                const { user } = await AuthAPI.login(email, password);
                set({ user, isAuthenticated: true, isLoading: false, error: null });

                return user;
            }

            catch (error: any) {
                update(state => ({
                    ...state,
                    isLoading: false,
                    error: error.message || 'Login failed'
                }));
                throw error;
            }

        },

        async register(userData: any) {
            update(state => ({ ...state, isLoading: true, error: null }));
            try {
                const { user } = await AuthAPI.register(userData);
                set({ user, isAuthenticated: true, isLoading: false, error: null });
                return user;
            } catch (error: any) {
                update(state => ({
                    ...state,
                    isLoading: false,
                    error: error.message || 'Registration failed'
                }));
                throw error;
            }
        },

        async logout() {
            update(state => ({ ...state, isLoading: true }));
            try {
                await AuthAPI.logout();
                set({ user: null, isAuthenticated: false, isLoading: false, error: null });
            } catch (error: any) {
                update(state => ({
                    ...state,
                    isLoading: false,
                    error: error.message || 'Logout failed'
                }));
            }
        },

        async checkAuth() {
            update(state => ({ ...state, isLoading: true }));
            try {
                const { user } = await AuthAPI.me();
                set({ user, isAuthenticated: true, isLoading: false, error: null });
                return user;
            } catch (error) {
                set({ user: null, isAuthenticated: false, isLoading: false, error: null });
                return null;
            }
        },

        clearError() {
            update(state => ({ ...state, error: null }));
        }
    };
}

export const authStore = createAuthStore();
