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