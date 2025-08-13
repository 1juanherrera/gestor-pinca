import { useQuery } from '@tanstack/react-query';
import { fetchFormulaciones } from '../Connection/getApi';

export const useFormulaciones = () => {

  const query = useQuery({
    queryKey: ['formulaciones'],
    queryFn: fetchFormulaciones,
  });

  const data = query.data ?? [];  

  const refreshData = () => {
    query.refetch();
  };

  const productos = data?.filter(item => item.tipo === 'producto') || [];
  const insumos = data?.filter(item => item.tipo === 'insumo') || [];

  return {
    ...query,
    data,
    productos,
    insumos,
    isLoading: query.isLoading,
    isError: query.isError,
    error: query.error,
    refreshData,
  }
}