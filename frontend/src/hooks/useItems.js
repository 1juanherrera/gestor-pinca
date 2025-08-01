import { useQuery } from '@tanstack/react-query';
import { fetchItems } from '../Connection/itemsApi';

export function useItems() {
  return useQuery({
    queryKey: ['items'],
    queryFn: fetchItems,
  });
}