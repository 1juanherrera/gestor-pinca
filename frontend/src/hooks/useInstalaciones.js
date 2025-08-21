import { useQuery } from '@tanstack/react-query';
import { fetchInstalaciones } from '../Connection/getApi';

export function useInstalaciones() {
  return useQuery({
    queryKey: ['instalaciones'],
    queryFn: fetchInstalaciones ,
  });
}