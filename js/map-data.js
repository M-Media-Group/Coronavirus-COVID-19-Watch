var json = covid_data.json_data;
var items = Object.keys(json).map(function(key, index) {
  var new_key = json[key]['All']['abbreviation'];
  var calculated_rate = json[key]['All']['comfirmed'] != 0 ? (json[key]['All']['confirmed'] / json[key]['All']['population']) * 100 : 0;
  var calculated_mortality_rate = json[key]['All']['comfirmed'] != 0 ? ((json[key]['All']['deaths'] / json[key]['All']['confirmed']) * 100) : 0;
  return {
    [new_key]: {
        confirmed: json[key]['All']['confirmed'],
        deaths: json[key]['All']['deaths'],
        population: json[key]['All']['population'],
        sq_km_area: json[key]['All']['sq_km_area'],
        population_infected: calculated_rate,
        mortality: calculated_mortality_rate,
    }
};
});

object = Object.assign({}, ...items);

var svgMapDataPopulation = {
  data: {

    confirmed: {
      name: 'Confirmed',
      thousandSeparator: ',',
    },
    deaths: {
      name: 'Deaths',
      thousandSeparator: ',',
    },
    population_infected: {
      name: 'Population infection rate',
      format: '{0}%',
      floatingNumbers: 1
    },
    mortality: {
      name: 'Mortality rate',
      format: '{0}%',
      floatingNumbers: 1
    },
    population: {
      name: 'Population',
      thousandSeparator: ',',
    },
    sq_km_area: {
      name: 'Square km (area)',
      thousandSeparator: ',',
    },
  },
  applyData: covid_data.color_by,
  values: object,
}
new svgMap({
  targetElementID: covid_data.selector,
  data: svgMapDataPopulation,
  flagType: 'image',
  attributionText: covid_data.attribution_text,
});