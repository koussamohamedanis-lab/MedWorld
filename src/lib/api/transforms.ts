/**
 * Converts snake_case keys to camelCase
 */
export function snakeToCamel(str: string): string {
    return str.replace(/_([a-z])/g, (g) => g[1].toUpperCase());
}

/**
 * Converts all snake_case keys in an object to camelCase recursively
 */
export function transformSnakeToCamel(obj: any): any {
    if (obj === null || obj === undefined) {
        return obj;
    }

    if (Array.isArray(obj)) {
        return obj.map((item) => transformSnakeToCamel(item));
    }

    if (typeof obj !== 'object') {
        return obj;
    }

    const result: any = {};
    for (const key in obj) {
        if (obj.hasOwnProperty(key)) {
            const camelKey = snakeToCamel(key);
            result[camelKey] = transformSnakeToCamel(obj[key]);
        }
    }

    return result;
}

/**
 * Converts camelCase keys to snake_case
 */
export function camelToSnake(str: string): string {
    return str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);
}

/**
 * Converts all camelCase keys in an object to snake_case recursively
 */
export function transformCamelToSnake(obj: any): any {
    if (obj === null || obj === undefined) {
        return obj;
    }

    if (Array.isArray(obj)) {
        return obj.map((item) => transformCamelToSnake(item));
    }

    if (typeof obj !== 'object') {
        return obj;
    }

    const result: any = {};
    for (const key in obj) {
        if (obj.hasOwnProperty(key)) {
            const snakeKey = camelToSnake(key);
            result[snakeKey] = transformCamelToSnake(obj[key]);
        }
    }

    return result;
}
