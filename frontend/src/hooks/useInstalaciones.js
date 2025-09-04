import { useApiMutation, useApiResource } from "../Connection/getApi";

export const useInstalaciones = () => {

  const query = useApiResource('/instalaciones');
  const mutation = useApiMutation('/instalaciones');
  const data = query.data ?? [];

  return {
    ...query,
    data,
    refreshData: query.refetch,
    create: mutation.mutate,
    isCreating: mutation.isLoading,
    createError: mutation.error,
  };
}
