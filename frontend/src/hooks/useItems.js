import { useApiMutation, useApiDelete, useApiUpdate, useApiResource } from "../connection/getApi";

export const useItems = () => {

  const query = useApiResource(`/item_general`);
  const mutation = useApiMutation('/item_general');
  const deleteMutation = useApiDelete(`/item_general`);
  const updateMutation = useApiUpdate(`/item_general`);

  const data = query.data ?? [];

  const materiaPrima = data.filter(item => item.tipo === '1');

  const remove = (id) => {
    deleteMutation.mutate(id, {
      onSuccess: () => query.refetch(),
    })
  }
  
  return {
    ...query,
    materiaPrima,
    refreshData: query.refetch,

    createItem: mutation.mutate,
    isCreating: mutation.isLoading,
    createError: mutation.error,
    
    updateItem: updateMutation.mutate,
    isUpdating: updateMutation.isLoading,
    updateError: updateMutation.error,

    removeItem: remove,
    isDeleting: deleteMutation.isLoading,
    deleteError: deleteMutation.error,
  }
}