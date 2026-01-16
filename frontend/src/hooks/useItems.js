import { useQueryClient } from "@tanstack/react-query";
import { useApiMutation, useApiDelete, useApiUpdate, useApiResource } from "../connection/getApi";

export const useItems = (id = null) => {
  const queryClient = useQueryClient();
  const ENDPOINT = '/item_general';

  const query = useApiResource(ENDPOINT);
  const mutation = useApiMutation(ENDPOINT);
  const deleteMutation = useApiDelete(ENDPOINT);
  const updateMutation = useApiUpdate(ENDPOINT);
  const itemDetail = useApiResource(id ? `${ENDPOINT}/${id}` : null, `item_detail_${id}`);

  const data = query.data ?? [];
  const itemDetailData = itemDetail.data ?? null;

  const materiaPrima = data.filter(item => item.tipo === '1');

  const createItem = (payload, options = {}) => {
    mutation.mutate(payload, {
      ...options,
      onSuccess: (...args) => {
        queryClient.invalidateQueries([ENDPOINT]);
        if (options.onSuccess) options.onSuccess(...args);
      },
    });
  };

  const updateItem = ({ id, data }, options = {}) => {
    updateMutation.mutate({ id, data }, {
      ...options,
      onSuccess: (...args) => {
        queryClient.invalidateQueries([ENDPOINT]);
        queryClient.invalidateQueries([`item_detail_${id}`])
        if (options.onSuccess) options.onSuccess(...args);
      },
    });
  };

  const remove = (idToDelete) => {
    deleteMutation.mutate(idToDelete, {
      onSuccess: () => queryClient.invalidateQueries([ENDPOINT]),
    });
  };

  return {
    ...query,
    materiaPrima,
    refreshData: () => queryClient.invalidateQueries([ENDPOINT]),
    itemDetail: itemDetailData,
    isLoadingItemDetail: itemDetail.isLoading,

    createItem, 
    isCreating: mutation.isLoading,
    createError: mutation.error,

    updateItem,
    isUpdating: updateMutation.isLoading,
    updateError: updateMutation.error,

    removeItem: remove,
    isDeleting: deleteMutation.isLoading,
    deleteError: deleteMutation.error,
  };
};