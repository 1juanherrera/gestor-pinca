import { useApiDelete, useApiMutation, useApiResource, useApiUpdate } from "../Connection/getApi";

export const useProveedores = () => {

  // Proveedores API hooks
  const query = useApiResource('/proveedor_items');
  const mutation = useApiMutation('/proveedores');
  const deleteMutation = useApiDelete("/proveedores");
  const updateMutation = useApiUpdate("/proveedores");

  // Item Proveedores API hooks
  const queryItem = useApiResource('/item_proveedores');
  const itemMutation = useApiMutation('/item_proveedores');
  const itemDeleteMutation = useApiDelete("/item_proveedores");
  const itemUpdateMutation = useApiUpdate("/item_proveedores");
  
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


    // Item Proveedores
    createItem: itemMutation.mutate,
    isCreatingItem: itemMutation.isLoading,
    createItemError: itemMutation.error,

    updateItem: itemUpdateMutation.mutate,
    isUpdatingItem: itemUpdateMutation.isLoading,
    updateItemError: itemUpdateMutation.error,
    
    removeItem: itemDeleteMutation.mutate,
    isDeletingItem: itemDeleteMutation.isLoading,
    deleteItemError: itemDeleteMutation.error,
  }
}