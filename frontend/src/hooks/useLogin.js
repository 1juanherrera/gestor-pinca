import { useApiMutation } from "../Connection/getApi";

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