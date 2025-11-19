import { useApiDelete, useApiMutation, useApiResource, useApiUpdate } from "../Connection/getApi";

export const useBodegas = (id) => {

  const query = useApiResource(`/instalaciones/bodegas/${id}`);
  const mutation = useApiMutation('/bodegas');
  const deleteMutation = useApiDelete(`/bodegas`);
  const updateMutation = useApiUpdate(`/bodegas`);

  const queryInventory = useApiResource(`/bodegas/inventario/${id}`);

  const data = query.data ?? [];
  const dataInventory = queryInventory.data ?? [];

  const remove = (id) => {
    deleteMutation.mutate(id, {
      onSuccess: () => query.refetch(),
    })
  }

  return {
    ...query,
    data,
    items: dataInventory,
    isLoadingItems: queryInventory.isLoading,
    errorItems: queryInventory.error,
    refreshItems: queryInventory.refetch,
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