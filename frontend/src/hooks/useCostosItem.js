import { useQueryClient } from "@tanstack/react-query";
import { useApiUpdate } from "../connection/getApi";

export const useCostosItem = () => {
  const queryClient = useQueryClient();
  const ENDPOINT = '/costos_item';

  const updateMutation = useApiUpdate(ENDPOINT);

    const updateItem = ({ id, data }, options = {}) => {
        updateMutation.mutate({ id, data }, {
            ...options,
            onSuccess: (...args) => {
                queryClient.invalidateQueries([ENDPOINT]);
                queryClient.invalidateQueries([`item_detail_${id}`])
                if (options.onSuccess) options.onSuccess(...args);
            }
        })
    }

  return {
    updateItem,
    isUpdating: updateMutation.isLoading,
    updateError: updateMutation.error,
  }
}