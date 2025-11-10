export const formatoPesoColombiano = (valor) => {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0 // Opcional: para mostrar sin decimales
  }).format(valor);
}

// Versión con decimales
export const formatoPesoColombiano2Decimales = (valor) => {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(valor);
}

// Formatear números sin símbolo de moneda
export const formatoNumeroColombiano = (valor) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(valor);
}

// Formatear cantidades
export const formatoCantidad = (valor, decimales = 2) => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: decimales,
    maximumFractionDigits: decimales
  }).format(valor);
}

// Parse a Colombian formatted currency string like "$ 48.000,00" into a number (48000)
export const parsePesoColombiano = (valor) => {
  if (valor == null) return 0;
  if (typeof valor === 'number') return valor;
  let s = String(valor).trim();
  // Remove any non digit, dot or comma characters (currency symbol, spaces)
  s = s.replace(/[^0-9.,-]/g, '');
  if (!s) return 0;
  // If string uses '.' as thousand separator and ',' as decimal (e.g. 48.000,00)
  // remove dots and replace comma with dot -> 48000.00
  const normalized = s.replace(/\./g, '').replace(/,/g, '.');
  const n = parseFloat(normalized);
  return Number.isFinite(n) ? n : 0;
}

// Generate a stable item id for items that may not have a numeric id.
// Priority: id_proveedor -> id -> codigo -> nombre+unidad (fallback).
export const stableItemId = (item = {}, providerPrefix = '') => {
  if (!item) return String(providerPrefix || '') + '-unknown';
  if (item.id_proveedor != null && item.id_proveedor !== '') return String(item.id_proveedor);
  if (item.id != null && item.id !== '') return String(item.id);
  if (item.codigo) return String(item.codigo);
  // fallback: use a deterministic combination of name + providerPrefix
  const name = (item.nombre || 'item').toString().replace(/\s+/g, '_');
  return `${providerPrefix ? providerPrefix + '-' : ''}${name}`;
}