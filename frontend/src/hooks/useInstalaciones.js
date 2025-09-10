import { useApiDelete, useApiMutation, useApiResource, useApiUpdate } from "../Connection/getApi";

export const useInstalaciones = () => {
  const query = useApiResource('/instalaciones');
  const mutation = useApiMutation('/instalaciones');
  const deleteMutation = useApiDelete("/instalaciones");
  const updateMutation = useApiUpdate("/instalaciones");

  const data = query.data ?? [];

  const remove = (id) => {
    deleteMutation.mutate(id, {
      onSuccess: () => query.refetch(),
    });
  };

  return {
    ...query,
    data,
    refreshData: query.refetch,

    create: mutation.mutate,
    isCreating: mutation.isLoading,
    createError: mutation.error,

    update: updateMutation.mutate,
    isUpdating: updateMutation.isLoading,
    updateError: updateMutation.error,

    remove,
    isDeleting: deleteMutation.isLoading,
    deleteError: deleteMutation.error,
  }
}