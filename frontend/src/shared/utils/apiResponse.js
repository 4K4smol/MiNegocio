export const unwrapApiData = (response) => response?.data ?? response;

export const unwrapApiCollection = (response) => {
    const data = unwrapApiData(response);

    if (Array.isArray(data)) return data;
    if (Array.isArray(data?.data)) return data.data;

    return [];
};
