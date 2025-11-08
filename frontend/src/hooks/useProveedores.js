import { useApiDelete, useApiMutation, useApiResource, useApiUpdate } from "../Connection/getApi";

export const useProveedores = () => {

  const query = useApiResource('/proveedor_items');
  const mutation = useApiMutation('/proveedores');
  const deleteMutation = useApiDelete("/proveedores");
  const updateMutation = useApiUpdate("/proveedores");
  const queryItem = useApiResource('/item_proveedores');
  
  const data = query.data ?? [];
  const itemData = queryItem.data ?? {};

  const remove = (id) => {
    deleteMutation.mutate(id, {
      onSuccess: () => query.refetch(),
    });
  };

  return {
    ...query,
    data,
    itemData,
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