import { useApiResource } from "../Connection/getApi";


export const useFormulaciones = () => {
  
  const query = useApiResource('/formulaciones');
  const data = query.data ?? [];

  const productos = data.filter(item => item.tipo === 'PRODUCTO');
  const insumos = data.filter(item => item.tipo === 'INSUMO');

  return {
    ...query,
    data,
    productos,
    insumos,
    refreshData: query.refetch,
  };
};