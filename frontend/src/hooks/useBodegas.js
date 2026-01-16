import { useApiDelete, useApiMutation, useApiResource, useApiUpdate } from "../connection/getApi";

export const useBodegas = (id) => {
  const isEnabled = !!id;

  const query = useApiResource(isEnabled ? `/instalaciones/bodegas/${id}` : null, `bodega_info_${id}`);

  const queryBodegas = useApiResource('/bodegas', 'bodegas_list');

  const queryInventory = useApiResource(isEnabled ? `/bodegas/inventario/${id}` : null, `bodega_inventario_${id}`);

  const mutation = useApiMutation('/bodegas');
  const deleteMutation = useApiDelete(`/bodegas`);
  const updateMutation = useApiUpdate(`/bodegas`);

  const data = query.data ?? [];
  const dataInventory = queryInventory.data ?? [];
  const dataBodegas = queryBodegas.data ?? [];

  const remove = (idToRemove) => {
    deleteMutation.mutate(idToRemove, {
      onSuccess: () => query.refetch(),
    });
  };

  return {
    ...query,
    data,
    bodegas: dataBodegas,
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