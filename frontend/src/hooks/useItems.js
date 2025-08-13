import { useQuery } from '@tanstack/react-query';
import { fetchItems } from '../Connection/getApi';

export function useItems() {
  return useQuery({
    queryKey: ['items'],
    queryFn: fetchItems,
  });
}