export const calcConfig = {
  regions: {
    'default': 1.0,
    'moscow': 1.3,
    'spb': 1.2,
    'voronezh': 1.0,
    'saratov': 0.95,
    'volgograd': 0.95,
    'tambov': 0.9,
  },
  
  delivery: {
    basePrice: 3500, // Базовая стоимость доставки
    perKm: 60,       // Цена за километр за городом
  },

  roof: {
    materials: {
      metallocherepitsa_gl: { name: 'Grand Line Металлочерепица', price: 650, brand: 'Grand Line', type: 'metal' },
      metallocherepitsa_mp: { name: 'Металл Профиль Монтеррей', price: 600, brand: 'Металл Профиль', type: 'metal' },
      profnastil_gl: { name: 'Grand Line Профнастил', price: 500, brand: 'Grand Line', type: 'metal' },
      profnastil_mp: { name: 'Металл Профиль Профнастил', price: 480, brand: 'Металл Профиль', type: 'metal' },
      soft_shinglas: { name: 'Технониколь Шинглас', price: 750, brand: 'Технониколь', type: 'soft' },
      soft_docke: { name: 'Docke Гибкая черепица', price: 800, brand: 'Docke', type: 'soft' },
      falts_gl: { name: 'Grand Line Кликфальц', price: 950, brand: 'Grand Line', type: 'falts' },
      composite: { name: 'Композитная черепица Luxard', price: 2100, brand: 'Luxard', type: 'premium' },
      ceramic: { name: 'Керамическая черепица Braas', price: 3200, brand: 'Braas', type: 'premium' },
    },
    extras: {
      hydro_vapor: { name: 'Гидро- и пароизоляция', price: 120, unit: 'm2' },
      battens: { name: 'Обрешетка и контробрешетка', price: 280, unit: 'm2' },
      insulation_200: { name: 'Утепление 200мм (каменная вата)', price: 650, unit: 'm2' },
      snow_guards: { name: 'Снегозадержатели трубчатые', price: 1100, unit: 'lm' },
      gutters: { name: 'Водосточная система', price: 850, unit: 'lm' },
      soffits: { name: 'Подшивка карнизных свесов (софиты)', price: 950, unit: 'lm' },
    },
    installation: {
      baseCold: 1300,
      baseWarm: 1900,
      typeCoef: { gable: 1.0, hip: 1.15, mansard: 1.25, flat: 0.95 },
      floorCoef: { '1': 1.0, '2': 1.1, '3': 1.2 },
    },
    dobornieRatio: 0.25, // Доборные элементы (коньки, планки) обычно составляют 20-30% от стоимости покрытия
  },

  facade: {
    materials: {
      siding_gl: { name: 'Grand Line Виниловый сайдинг', price: 380, brand: 'Grand Line' },
      siding_docke: { name: 'Docke Premium Сайдинг', price: 420, brand: 'Docke' },
      metal_siding_gl: { name: 'Grand Line Металлосайдинг', price: 580, brand: 'Grand Line' },
      metal_siding_mp: { name: 'Металл Профиль Металлосайдинг', price: 550, brand: 'Металл Профиль' },
      panels_gl: { name: 'Grand Line Я-Фасад', price: 750, brand: 'Grand Line' },
      panels_docke: { name: 'Docke-R Панели', price: 780, brand: 'Docke' },
      hauberk_tn: { name: 'Технониколь Hauberk', price: 900, brand: 'Технониколь' },
      fibro_cedral: { name: 'Фиброцементный сайдинг Cedral', price: 1950, brand: 'Cedral' },
      plaster: { name: 'Штукатурный фасад (Короед)', price: 850, brand: 'Ceresit' },
    },
    extras: {
      subsystem_wood: { name: 'Деревянная подсистема', price: 250, unit: 'm2' },
      subsystem_metal: { name: 'Металлическая подсистема', price: 450, unit: 'm2' },
      membrane: { name: 'Ветро-влагозащитная мембрана', price: 100, unit: 'm2' },
      insulation_50: { name: 'Утепление 50мм', price: 250, unit: 'm2' },
      insulation_100: { name: 'Утепление 100мм', price: 450, unit: 'm2' },
      window_trims: { name: 'Откосы и отливы для окон', price: 600, unit: 'pcs' },
    },
    installation: {
      baseSiding: 900,
      basePanels: 1100,
      baseFibro: 1400,
      basePlaster: 1600,
    },
    dobornieRatio: 0.30, // Углы, стартовые, J-профили (около 30% от материала фасада)
  },

  fence: {
    materials: {
      profnastil_gl: { name: 'Grand Line Профнастил', price: 480, brand: 'Grand Line' },
      profnastil_mp: { name: 'Металл Профиль Профнастил', price: 450, brand: 'Металл Профиль' },
      shtaketnik_gl: { name: 'Grand Line Евроштакетник', price: 520, brand: 'Grand Line' },
      shtaketnik_mp: { name: 'Металл Профиль Евроштакетник', price: 490, brand: 'Металл Профиль' },
      mesh_3d: { name: '3D-сетка Гиттер', price: 410, brand: 'Гиттер' },
      rabitza: { name: 'Сетка-рабица', price: 180, brand: 'NoName' },
      jalousie: { name: 'Забор-жалюзи', price: 1400, brand: 'Grand Line' },
      rancho: { name: 'Забор Ранчо', price: 1250, brand: 'Grand Line' },
    },
    extras: {
      pillars: { name: 'Столбы 60х60х2', price: 900, unit: 'pcs' },
      lags: { name: 'Лаги 40х20х1.5', price: 180, unit: 'lm' },
      screws: { name: 'Крепеж', price: 50, unit: 'm' },
      concrete: { name: 'Бетонирование столбов', price: 600, unit: 'pcs' },
      brick_pillars: { name: 'Кирпичные столбы', price: 8000, unit: 'pcs' },
      gate_swing: { name: 'Ворота распашные + калитка', price: 25000, unit: 'set' },
      gate_sliding: { name: 'Ворота откатные с автоматикой', price: 85000, unit: 'set' },
    },
    installation: {
      baseProfnastil: 600,
      baseShtaketnik: 700,
      base3D: 500,
      baseJalousie: 900,
    }
  }
};
