import { useApiResource } from "../Connection/getApi";


export const useProveedores = () => {

  const query = useApiResource('/proveedores');
  const data = query.data ?? [];

  return {
    ...query,
    data,
    refreshData: query.refetch,
  }
}