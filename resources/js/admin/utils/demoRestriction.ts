export function isDemoRestrictionError(error: any): boolean {
    if (!error) {
        return false;
    }

    const data = error?.response?.data;

    return Boolean(
        error.__demoRestrictionHandled ||
        data?.is_demo_restriction ||
        (typeof window !== 'undefined' && (window as any).__polycmsDemoRestrictionActive === true)
    );
}
