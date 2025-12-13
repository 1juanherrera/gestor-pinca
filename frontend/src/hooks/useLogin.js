import { useApiMutation } from "../connection/getApi";

export const useLogin = () => {

    const { mutateAsync, isLoading, isError, error, data } = useApiMutation(`/login`);

    return {
        login: mutateAsync,
        isLoading,
        isError,
        error,
        data,
    }
}