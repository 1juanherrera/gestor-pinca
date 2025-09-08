import { useApiResource } from "../Connection/getApi";


export const useEmpresa = () => {
  
  const query = useApiResource('/empresa');
  const data = query.data ?? [];

  return {
    ...query,
    data,
    refreshData: query.refetch,
  }
}