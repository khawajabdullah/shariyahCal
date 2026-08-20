import { CONSTELLATION_COUNTRIES } from './constellation';

/** Brand template defaults — used until directory data is complete. */
export const TEMPLATE_HERO_STATS = {
  scholars: 8,
  countries: CONSTELLATION_COUNTRIES.length,
  madhahib: 4,
  languages: 9,
};

/**
 * @param {Array} scholars
 * @param {{ madhahib?: string[], languages?: string[] }} filters
 */
export function computeHeroStats(scholars = [], filters = {}) {
  if (!scholars.length) {
    return { ...TEMPLATE_HERO_STATS };
  }

  const countries = new Set(scholars.map((s) => s.country).filter(Boolean)).size;
  const madhahibFromFilters = Array.isArray(filters.madhahib) ? filters.madhahib.length : 0;
  const languagesFromFilters = Array.isArray(filters.languages) ? filters.languages.length : 0;
  const madhahibFromScholars = new Set(scholars.map((s) => s.madhhab).filter(Boolean)).size;
  const languagesFromScholars = new Set(scholars.flatMap((s) => s.languages || [])).size;

  return {
    scholars: scholars.length,
    countries: countries || TEMPLATE_HERO_STATS.countries,
    madhahib: madhahibFromFilters || madhahibFromScholars || TEMPLATE_HERO_STATS.madhahib,
    languages: languagesFromFilters || languagesFromScholars || TEMPLATE_HERO_STATS.languages,
  };
}
