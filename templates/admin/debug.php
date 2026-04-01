<?php
defined('ABSPATH') || exit;
?>
<div class="wrap naba-hdwp-admin">
  <h1>Naba HDWP Overview</h1>

  <div class="wrapper">
    <div class="left">
      <h3>Current Settings</h3>
      <code>
        <pre>{ "leagues": <?php echo esc_html($form_data['leagues']); ?> }</pre>
      </code>
    </div>
    <div class="right">
      <h3>Demo Settings</h3>
      <code>
        <pre>{ "leagues": [
        {
            "id": 1,
            "name": "ÖEL",
            "seasons": [
                {
                    "divisionId": 18808,
                    "standings": [
                        {
                            "divisionId": 18853,
                            "playoffCut": 4,
                            "name": "Gruppe West"
                        },
                        {
                            "divisionId": 18852,
                            "playoffCut": 4,
                            "name": "Gruppe Ost"
                        }
                    ],
                    "playoffId": null,
                    "teamId": 34181,
                    "seasonLabel": "2025-26",
                    "streamingFallbackUrl": "https://www.red.sport/en-at/page/oel"
                },
                {
                    "divisionId": 16262,
                    "standings": [
                        {
                            "divisionId": 16266,
                            "playoffCut": 4,
                            "name": "Gruppe West"
                        },
                        {
                            "divisionId": 16265,
                            "playoffCut": 4,
                            "name": "Gruppe Ost"
                        }
                    ],
                    "playoffId": 17392,
                    "teamId": 34181,
                    "seasonLabel": "2024-25",
                    "streamingFallbackUrl": "https://www.red.sport/en-at/page/oel"
                },
                {
                    "divisionId": 13477,
                    "standings": [
                        {
                            "divisionId": 13481,
                            "playoffCut": 4,
                            "name": "Gruppe West"
                        },
                        {
                            "divisionId": 13480,
                            "playoffCut": 4,
                            "name": "Gruppe Ost"
                        }
                    ],
                    "playoffId": 14630,
                    "teamId": 34181,
                    "seasonLabel": "2023-24"
                },
                {
                    "divisionId": 11058,
                    "standings": [
                        {
                            "divisionId": 11061,
                            "playoffCut": 3,
                            "name": "Gruppe West"
                        },
                        {
                            "divisionId": 11062,
                            "playoffCut": 3,
                            "name": "Gruppe Nord-Ost"
                        },
                        {
                            "divisionId": 11063,
                            "playoffCut": 2,
                            "name": "Gruppe Süd"
                        }
                    ],
                    "playoffId": 11852,
                    "teamId": 34181,
                    "seasonLabel": "2022-23"
                },
                {
                    "divisionId": 9128,
                    "standings": [
                        {
                            "divisionId": 9135,
                            "playoffCut": 3,
                            "name": "Gruppe West"
                        },
                        {
                            "divisionId": 9136,
                            "playoffCut": 3,
                            "name": "Gruppe Nord-Ost"
                        },
                        {
                            "divisionId": 9137,
                            "playoffCut": 2,
                            "name": "Gruppe Süd"
                        }
                    ],
                    "playoffId": 9658,
                    "teamId": 34181,
                    "seasonLabel": "2021-22"
                }
            ]
        },
        {
            "id": 6,
            "name": "Damen",
            "seasons": [
                {
                    "divisionId": 19404,
                    "standings": [
                        {
                            "divisionId": 19404
                        }
                    ],
                    "playoffId": null,
                    "teamId": 41347,
                    "seasonLabel": "2025-26",
                    "streamingFallbackUrl": "https://www.red.sport/en-at/page/debl2"
                },
                {
                    "divisionId": 16274,
                    "standings": [
                        {
                            "divisionId": 16274
                        }
                    ],
                    "playoffId": null,
                    "teamId": 41347,
                    "seasonLabel": "2024-25",
                    "streamingFallbackUrl": "https://www.red.sport/en-at/page/debl2"
                },
                {
                    "divisionId": 13642,
                    "standings": [
                        {
                            "divisionId": 13645
                        }
                    ],
                    "playoffId": 14879,
                    "teamId": 41347,
                    "seasonLabel": "2023-24"
                },
                {
                    "divisionId": 11192,
                    "standings": [
                        {
                            "divisionId": 11195
                        }
                    ],
                    "playoffId": 12509,
                    "teamId": 41347,
                    "seasonLabel": "2022-23"
                }
            ],
            "isWomanCup": true
        },
        {
            "id": 2,
            "name": "VEHL1",
            "seasons": [
                {
                    "divisionId": 19007,
                    "standings": [
                        {
                            "divisionId": 19007,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": null,
                    "teamId": 34431,
                    "seasonLabel": "2025-26"
                },
                {
                    "divisionId": 16601,
                    "standings": [
                        {
                            "divisionId": 16601,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 17409,
                    "teamId": 34431,
                    "seasonLabel": "2024-25"
                },
                {
                    "divisionId": 13686,
                    "standings": [
                        {
                            "divisionId": 13686,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 14812,
                    "teamId": 34431,
                    "seasonLabel": "2023-24"
                },
                {
                    "divisionId": 11002,
                    "standings": [
                        {
                            "divisionId": 11002,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 11943,
                    "teamId": 731,
                    "seasonLabel": "2022-23"
                },
                {
                    "divisionId": 9188,
                    "standings": [
                        {
                            "divisionId": 9188,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": null,
                    "teamId": 34431,
                    "seasonLabel": "2021-22"
                }
            ]
        },
        {
            "id": 3,
            "name": "VEHL2",
            "seasons": [
                {
                    "divisionId": 19008,
                    "standings": [
                        {
                            "divisionId": 19008,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": null,
                    "teamId": 731,
                    "seasonLabel": "2025-26"
                },
                {
                    "divisionId": 16657,
                    "standings": [
                        {
                            "divisionId": 16657,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 17344,
                    "teamId": 6740,
                    "seasonLabel": "2024-25"
                },
                {
                    "divisionId": 13687,
                    "standings": [
                        {
                            "divisionId": 13687,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 14511,
                    "teamId": 6740,
                    "seasonLabel": "2023-24"
                },
                {
                    "divisionId": 9189,
                    "standings": [
                        {
                            "divisionId": 9189,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": null,
                    "teamId": 731,
                    "seasonLabel": "2021-22"
                }
            ]
        },
        {
            "id": 4,
            "name": "VEHL3",
            "seasons": [
                {
                    "divisionId": 19009,
                    "standings": [
                        {
                            "divisionId": 19009,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": null,
                    "teamId": null,
                    "seasonLabel": "2025-26"
                },
                {
                    "divisionId": 16658,
                    "standings": [
                        {
                            "divisionId": 16658,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 17405,
                    "teamId": 731,
                    "seasonLabel": "2024-25"
                },
                {
                    "divisionId": 13688,
                    "standings": [
                        {
                            "divisionId": 11004,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 14786,
                    "teamId": 731,
                    "seasonLabel": "2023-24"
                },
                {
                    "divisionId": 11004,
                    "standings": [
                        {
                            "divisionId": 11004,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 11932,
                    "teamId": 34457,
                    "seasonLabel": "2022-23"
                }
            ]
        },
        {
            "id": 5,
            "name": "VEHL4",
            "seasons": [
                {
                    "divisionId": 19010,
                    "standings": [
                        {
                            "divisionId": 19010,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": null,
                    "teamId": 49205,
                    "seasonLabel": "2025-26"
                },
                {
                    "divisionId": 16659,
                    "standings": [
                        {
                            "divisionId": 16659,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 17521,
                    "teamId": 49205,
                    "seasonLabel": "2024-25"
                },
                {
                    "divisionId": 13689,
                    "standings": [
                        {
                            "divisionId": 13689,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 14495,
                    "teamId": 49205,
                    "seasonLabel": "2023-24"
                },
                {
                    "divisionId": 11005,
                    "standings": [
                        {
                            "divisionId": 11005,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 11724,
                    "teamId": 0,
                    "seasonLabel": "2022-23"
                },
                {
                    "divisionId": 9191,
                    "standings": [
                        {
                            "divisionId": 9191,
                            "playoffCut": 4
                        }
                    ],
                    "playoffId": 11723,
                    "teamId": 34457,
                    "seasonLabel": "2021-22"
                }
            ]
        }
    ]
}</pre>
      </code>
    </div>
  </div>
</div>

<style>
  .naba-hdwp-admin {
    padding: 10px 30px 0 20px;
  }

  .wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
</style>
