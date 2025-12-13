import { useApiDelete, useApiMutation, useApiResource, useApiUpdate } from "../connection/getApi";

export const useFacturas = () => {

  const query = useApiResource(`/facturas`);
  const mutation = useApiMutation('/facturas');
  const deleteMutation = useApiDelete(`/facturas`);
  const updateMutation = useApiUpdate(`/facturas`);

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

    createFactura: mutation.mutate,
    isCreating: mutation.isLoading,
    createError: mutation.error,

    updateFactura: updateMutation.mutate,
    isUpdating: updateMutation.isLoading,
    updateError: updateMutation.error,

    removeFactura: remove,
    isDeleting: deleteMutation.isLoading,
    deleteError: deleteMutation.error,
  };
};