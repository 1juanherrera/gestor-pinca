import { useApiDelete, useApiMutation, useApiResource, useApiUpdate } from "../connection/getApi";

export const useClientes = () => {
  const query = useApiResource(`/clientes`);
  const mutation = useApiMutation('/clientes');
  const deleteMutation = useApiDelete(`/clientes`);
  const updateMutation = useApiUpdate(`/clientes`);

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

    createCliente: mutation.mutate,
    isCreating: mutation.isLoading,
    createError: mutation.error,

    updateCliente: updateMutation.mutate,
    isUpdating: updateMutation.isLoading,
    updateError: updateMutation.error,

    removeCliente: remove,
    isDeleting: deleteMutation.isLoading,
    deleteError: deleteMutation.error,
  };
};