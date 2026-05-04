exports.avaliarDia = (caloriasIngeridas, caloriasGastas, tmb) => {
  const saldo = caloriasIngeridas - tmb;
  const deficit_total = caloriasGastas - Math.max(0, saldo);

  let estado;
  if (caloriasIngeridas <= tmb) {
    estado = caloriasIngeridas < tmb * 0.9 ? 'deficit' : 'equilibrio';
  } else {
    estado = 'excesso';
  }

  const perdaEstimada_g = Math.max(0, caloriasGastas / 7.7); // 1g gordura ≈ 7.7 kcal

  return {
    saldo_calorico: Math.round(saldo),
    deficit_total: Math.round(deficit_total),
    estado,
    objetivo_cumprido: estado !== 'excesso',
    perda_estimada_g: Math.round(perdaEstimada_g),
    mensagem: estado === 'deficit'   ? 'Excelente! Está em défice calórico.' :
              estado === 'equilibrio'? 'Dentro dos objectivos. Continue assim.' :
                                      'Ingeriu mais calorias do que o necessário.'
  };
};
