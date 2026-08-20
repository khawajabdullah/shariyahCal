/** Country labels in first-appearance order from the brand template SCHOLARS list. */
export const CONSTELLATION_COUNTRIES = [
  'Pakistan',
  'Mauritania',
  'Saudi Arabia',
  'Malaysia',
  'United Kingdom',
  'Bahrain',
  'Kuwait',
];

export function getConstellationNodes(countries = CONSTELLATION_COUNTRIES) {
  const cx = 50;
  const cy = 50;
  return countries.map((label, i) => {
    const angle = (i / countries.length) * Math.PI * 2 - Math.PI / 2;
    const r = 34 + (i % 3) * 4;
    return {
      label,
      x: cx + Math.cos(angle) * r,
      y: cy + Math.sin(angle) * r,
    };
  });
}
