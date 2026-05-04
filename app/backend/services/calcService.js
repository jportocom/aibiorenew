exports.calcular = (user) => {
  const { peso, altura, idade, sexo, atividade } = user;

  // Fórmula de Mifflin-St Jeor
  let tmb;
  if (sexo === 'M') {
    tmb = (10 * peso) + (6.25 * (altura * 100)) - (5 * idade) + 5;
  } else {
    tmb = (10 * peso) + (6.25 * (altura * 100)) - (5 * idade) - 161;
  }

  const imc = peso / (altura * altura);

  const fatoresAtividade = {
    sedentario: 1.2,
    moderado:   1.55,
    ativo:      1.9
  };
  const tdee = tmb * (fatoresAtividade[atividade] || 1.2);

  const classificacaoImc = imc < 18.5 ? 'Abaixo do peso'
    : imc < 25   ? 'Peso normal'
    : imc < 30   ? 'Excesso de peso'
    : imc < 35   ? 'Obesidade grau I'
    : imc < 40   ? 'Obesidade grau II'
    :               'Obesidade grau III';

  return {
    imc: parseFloat(imc.toFixed(2)),
    classificacao_imc: classificacaoImc,
    tmb: Math.round(tmb),
    tdee: Math.round(tdee),
    peso_saudavel_min: parseFloat((18.5 * altura * altura).toFixed(1)),
    peso_saudavel_max: parseFloat((24.9 * altura * altura).toFixed(1))
  };
};
