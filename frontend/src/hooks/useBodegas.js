import { useApiDelete, useApiMutation, useApiResource, useApiUpdate } from "../Connection/getApi";

export const useBodegas = (idInstalacion) => {
  const query = useApiResource(`/instalaciones/bodegas/${idInstalacion}`);
  const mutation = useApiMutation('/bodegas');
  const deleteMutation = useApiDelete(`/bodegas`);
  const updateMutation = useApiUpdate(`/bodegas`);

  const data = query.data ?? [];

  const remove = (id) => {
    deleteMutation.mutate(id, {
      onSuccess: () => query.refetch(),
    })
  }
  

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
  };
};