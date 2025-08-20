import { useQuery } from '@tanstack/react-query';
import { fetchBodegas } from '../Connection/getApi';

export function useBodegas() {
  return useQuery({
    queryKey: ['bodegas'],
    queryFn: fetchBodegas,
  });
}