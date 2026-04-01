class HdWpAdminLeagueConfigurator {
  constructor(editorSelector, textareaSelector) {
    this.editor = document.querySelector(editorSelector);
    this.leaguesField = document.querySelector(textareaSelector);
    this.leagues = [];
    this.state = { openLeagues: {}, openSeasons: {} };

    try {
      this.leagues = JSON.parse(this.leaguesField.value || '[]');
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error(e);
    }

    this.loadOpenState();
    this.init();
  }

  loadOpenState() {
    const saved = localStorage.getItem('naba_hdwp_open_state');

    if (saved) {
      try {
        const obj = JSON.parse(saved);

        this.state.openLeagues = obj.openLeagues || {};
        this.state.openSeasons = obj.openSeasons || {};
      } catch (e) {
        // eslint-disable-next-line no-console
        console.error(e);
      }
    }
  }

  saveOpenState() {
    localStorage.setItem(
      'naba_hdwp_open_state',
      JSON.stringify({
        openLeagues: this.state.openLeagues,
        openSeasons: this.state.openSeasons,
      }),
    );
  }

  toggleLeague(idx) {
    this.state.openLeagues[idx] = !this.state.openLeagues[idx];
    const body = this.editor.querySelector(`.league-box[data-idx="${idx}"] .panel-body`);
    const arrow = this.editor.querySelector(`.league-box[data-idx="${idx}"] .icon-arrow`);

    if (body) body.classList.toggle('collapsed', !this.state.openLeagues[idx]);

    if (arrow) arrow.classList.toggle('collapsed', !this.state.openLeagues[idx]);
    this.saveOpenState();
  }

  toggleSeason(lidx, sidx) {
    const key = `${lidx}-${sidx}`;

    this.state.openSeasons[key] = !this.state.openSeasons[key];
    const body = this.editor.querySelector(`#seasons-${lidx} .season-box[data-sidx="${sidx}"] .panel-body`);
    const arrow = this.editor.querySelector(`#seasons-${lidx} .season-box[data-sidx="${sidx}"] .icon-arrow`);

    if (body) body.classList.toggle('collapsed', !this.state.openSeasons[key]);

    if (arrow) arrow.classList.toggle('collapsed', !this.state.openSeasons[key]);
    this.saveOpenState();
  }

  toggleStanding(lidx, sidx, stidx) {
    const key = `${lidx}-${sidx}-${stidx}`;

    this.state.openSeasons[key] = !this.state.openSeasons[key];
    const body = this.editor.querySelector(
      `#standings-${lidx}-${sidx} .standing-box[data-stidx="${stidx}"] .panel-body`,
    );
    const arrow = this.editor.querySelector(
      `#standings-${lidx}-${sidx} .standing-box[data-stidx="${stidx}"] .icon-arrow`,
    );

    if (body) body.classList.toggle('collapsed', !this.state.openSeasons[key]);

    if (arrow) arrow.classList.toggle('collapsed', !this.state.openSeasons[key]);
    this.saveOpenState();
  }

  init() {
    this.editor.addEventListener('click', (e) => this.handleClick(e));
    this.editor.addEventListener('input', (e) => this.handleInput(e));
    this.editor.addEventListener('change', (e) => this.handleInput(e));
    this.render();
    this.initDragDrop();
  }

  handleInput(e) {
    // eslint-disable-next-line id-length
    const t = e.target;
    const value = t.type === 'checkbox' ? t.checked : t.value;

    if (t.dataset.idx !== undefined && t.dataset.lidx === undefined) {
      const { idx } = t.dataset;

      this.leagues[idx][t.dataset.field] = t.type === 'number' ? Number(value) : value;
    }

    if (t.dataset.lidx !== undefined && t.dataset.sidx !== undefined && t.dataset.stidx === undefined) {
      const { lidx } = t.dataset;
      const { sidx } = t.dataset;

      this.leagues[lidx].seasons[sidx][t.dataset.field] = t.type === 'number' ? Number(value) : value;
    }

    if (t.dataset.lidx !== undefined && t.dataset.sidx !== undefined && t.dataset.stidx !== undefined) {
      const { lidx } = t.dataset;
      const { sidx } = t.dataset;
      const { stidx } = t.dataset;

      this.leagues[lidx].seasons[sidx].standings[stidx][t.dataset.field] = t.type === 'number' ? Number(value) : value;
    }

    this.updateJSON();
  }

  updateJSON() {
    this.leaguesField.value = JSON.stringify(this.leagues, null, 2);
  }

  render() {
    this.editor.innerHTML = '';
    this.leagues.forEach((league, idx) => this.renderLeague(league, idx));

    const addBtn = document.createElement('button');

    addBtn.type = 'button';
    addBtn.className = 'naba-hdwp-button--default';
    addBtn.textContent = '+ Liga hinzufügen';
    addBtn.addEventListener('click', () => {
      this.leagues.push({ id: Date.now(), name: '', isWomanCup: false, seasons: [] });
      this.state.openLeagues[this.leagues.length - 1] = true;
      this.updateJSON();
      this.render();
      this.initDragDrop();
    });
    this.editor.append(addBtn);
    this.initDragDrop();
  }

  renderLeague(league, idx) {
    const div = document.createElement('div');

    div.className = 'league-box draggable-item';
    div.dataset.idx = idx;
    div.innerHTML = `
      <div class="panel-header" data-idx="${idx}">
        <span class="drag-handle">☰</span>
        <span>${league.name || 'Unnamed League'}</span>
        <span class="icon-arrow ${this.state.openLeagues[idx] ? '' : 'collapsed'}">▼</span>
      </div>
      <div class="panel-body ${this.state.openLeagues[idx] ? '' : 'collapsed'}">
        <input type="number" data-field="id" data-idx="${idx}" value="${league.id || ''}" style="display: none;">
        <div class="naba-hdwp-panel-box">
          <label for="name">Liga Name</label>
          <input type="text" data-field="name" data-idx="${idx}" value="${league.name || ''}">
        </div>
        <div class="naba-hdwp-panel-box">
          <label for="isWomanCup">Damen-Liga</label>
          <input type="checkbox" data-field="isWomanCup" data-idx="${idx}" ${league.isWomanCup ? 'checked' : ''}>
        </div>
        <button type="button" class="remove-league remove-button" data-idx="${idx}">
           ${league.name || 'Unnamed League'} - Liga löschen
        </button>
        <div class="naba-hdwp-leagues-list-container-header">
          Saisonen - ${league.name || 'Unnamed League'}
        </div>
        <div class="naba-hdwp-leagues-list-container draggable-list" id="seasons-${idx}"></div>
      </div>
    `;
    this.editor.append(div);
    this.renderSeasons(idx);
  }

  renderSeasons(lidx) {
    const league = this.leagues[lidx];
    const container = document.getElementById(`seasons-${lidx}`);

    if (!container) return;
    container.innerHTML = '';
    (league.seasons || []).forEach((season, sIdx) => {
      const div = document.createElement('div');

      div.className = 'season-box draggable-item';
      div.dataset.sidx = sIdx;
      div.innerHTML = `
        <div class="panel-header" data-lidx="${lidx}" data-sidx="${sIdx}">
          <span class="drag-handle">☰</span>
          <span>${season.seasonLabel || 'Unnamed Season'}</span>
          <span class="icon-arrow ${this.state.openSeasons[`${lidx}-${sIdx}`] ? '' : 'collapsed'}">▼</span>
        </div>
        <div class="panel-body ${this.state.openSeasons[`${lidx}-${sIdx}`] ? '' : 'collapsed'}">
          <div class="naba-hdwp-panel-box">
            <label for="seasonLabel">Season Label</label>
            <input type="text" data-field="seasonLabel" data-lidx="${lidx}" data-sidx="${sIdx}" value="${season.seasonLabel || ''}">
          </div>
          <div class="naba-hdwp-panel-box">
            <label for="divisionId">Division ID</label>
            <input type="number" data-field="divisionId" data-lidx="${lidx}" data-sidx="${sIdx}" value="${season.divisionId || ''}">
          </div>
          <div class="naba-hdwp-panel-box">
            <label for="playoffId">Playoff Division ID</label>
            <input type="number" data-field="playoffId" data-lidx="${lidx}" data-sidx="${sIdx}" value="${season.playoffId || ''}">
          </div>
          <div class="naba-hdwp-panel-box">
            <label for="teamId">Highlight Team ID</label>
            <input type="number" data-field="teamId" data-lidx="${lidx}" data-sidx="${sIdx}" value="${season.teamId || ''}">
          </div>
          <button type="button" class="remove-season remove-button" data-lidx="${lidx}" data-sidx="${sIdx}">
            ${season.seasonLabel || 'Unnamed Season'} - Saison löschen
          </button>
          <div class="naba-hdwp-leagues-list-container-header">
            Tabellen der Saison: ${season.seasonLabel || 'Unnamed Season'}
          </div>
          <div class="naba-hdwp-leagues-list-container draggable-list" id="standings-${lidx}-${sIdx}"></div>
        </div>
      `;
      container.append(div);
      this.renderStandings(lidx, sIdx);
    });

    const addBtn = document.createElement('button');

    addBtn.type = 'button';
    addBtn.className = 'naba-hdwp-button--default';
    addBtn.textContent = '+ Saison hinzufügen';
    addBtn.addEventListener('click', () => {
      league.seasons = league.seasons || [];
      league.seasons.push({ divisionId: 0, playoffId: 0, teamId: 0, seasonLabel: '', standings: [] });
      this.updateJSON();
      this.renderSeasons(lidx);
      this.initDragDrop();
    });
    container.append(addBtn);
  }

  renderStandings(lidx, sidx) {
    const season = this.leagues[lidx].seasons[sidx];
    const container = document.getElementById(`standings-${lidx}-${sidx}`);

    if (!container) return;
    container.innerHTML = '';
    (season.standings || []).forEach((standing, stIdx) => {
      const div = document.createElement('div');

      div.className = 'standing-box draggable-item';
      div.dataset.stidx = stIdx;
      div.innerHTML = `
        <div class="panel-header" data-lidx="${lidx}" data-sidx="${sidx}" data-stidx="${stIdx}">
          <span class="drag-handle">☰</span>
          <span>${standing.name || 'Unnamed Standing'}</span>
          <span class="icon-arrow ${this.state.openSeasons[`${lidx}-${sidx}-${stIdx}`] ? '' : 'collapsed'}">▼</span>
        </div>
        <div class="panel-body ${this.state.openSeasons[`${lidx}-${sidx}-${stIdx}`] ? '' : 'collapsed'}">
          <div class="naba-hdwp-panel-box">
            <label>Name</label>
            <input type="text" data-field="name" data-lidx="${lidx}" data-sidx="${sidx}" data-stidx="${stIdx}" value="${standing.name || ''}">
          </div>
          <div class="naba-hdwp-panel-box">
            <label>Division ID</label>
            <input type="number" data-field="divisionId" data-lidx="${lidx}" data-sidx="${sidx}" data-stidx="${stIdx}" value="${standing.divisionId || ''}">
          </div>
          <div class="naba-hdwp-panel-box">
            <label>Playoff Cut</label>
            <input type="number" data-field="playoffCut" data-lidx="${lidx}" data-sidx="${sidx}" data-stidx="${stIdx}" value="${standing.playoffCut || ''}">
          </div>
          <button type="button" class="remove-standing remove-button" data-lidx="${lidx}" data-sidx="${sidx}" data-stidx="${stIdx}">
            ${standing.name || 'Unnamed Standing'} - Tabelle löschen
          </button>
        </div>
      `;
      container.append(div);
    });

    const addBtn = document.createElement('button');

    addBtn.type = 'button';
    addBtn.className = 'naba-hdwp-button--default';
    addBtn.textContent = '+ Tabelle hinzufügen';
    addBtn.addEventListener('click', () => {
      season.standings = season.standings || [];
      season.standings.push({ divisionId: 0, playoffCut: 0, name: '' });
      this.updateJSON();
      this.renderStandings(lidx, sidx);
      this.initDragDrop();
    });
    container.append(addBtn);
  }

  initDragDrop() {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars, no-unused-vars, no-undef
    const sortableLeagues = new Sortable(this.editor, { handle: '.drag-handle', animation: 150 });

    // eslint-disable-next-line id-length
    this.leagues.forEach((l, idx) => {
      const seasonsContainer = document.getElementById(`seasons-${idx}`);

      // eslint-disable-next-line no-undef
      if (seasonsContainer) new Sortable(seasonsContainer, { handle: '.drag-handle', animation: 150 });
      // eslint-disable-next-line id-length
      l.seasons.forEach((s, sIdx) => {
        const standingsContainer = document.getElementById(`standings-${idx}-${sIdx}`);

        // eslint-disable-next-line no-undef
        if (standingsContainer) new Sortable(standingsContainer, { handle: '.drag-handle', animation: 150 });
      });
    });
  }

  handleClick(e) {
    // Find the closest .panel-header or button
    const header = e.target.closest('.panel-header');
    const button = e.target.closest('button');

    if (header) {
      // Read dataset values from the header itself
      const lidx = header.dataset.lidx ?? header.dataset.idx;
      const { sidx } = header.dataset;
      const { stidx } = header.dataset;

      if (lidx !== undefined && sidx === undefined) this.toggleLeague(lidx);
      else if (lidx !== undefined && sidx !== undefined && stidx === undefined) this.toggleSeason(lidx, sidx);
      else if (lidx !== undefined && sidx !== undefined && stidx !== undefined) this.toggleStanding(lidx, sidx, stidx);

      return;
    }

    if (button) {
      // Remove buttons with confirmation
      if (button.classList.contains('remove-league') && confirm('Delete league?')) {
        const lidx = button.dataset.idx;

        this.leagues.splice(lidx, 1);
        delete this.state.openLeagues[lidx];
        this.updateJSON();
        this.render();
        this.initDragDrop();
      }

      if (button.classList.contains('remove-season') && confirm('Delete season?')) {
        const { lidx } = button.dataset;
        const { sidx } = button.dataset;

        this.leagues[lidx].seasons.splice(sidx, 1);
        delete this.state.openSeasons[`${lidx}-${sidx}`];
        this.updateJSON();
        this.renderSeasons(lidx);
        this.initDragDrop();
      }

      if (button.classList.contains('remove-standing') && confirm('Delete standing?')) {
        const { lidx } = button.dataset;
        const { sidx } = button.dataset;
        const { stidx } = button.dataset;

        this.leagues[lidx].seasons[sidx].standings.splice(stidx, 1);
        this.updateJSON();
        this.renderStandings(lidx, sidx);
        this.initDragDrop();
      }
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  new HdWpAdminLeagueConfigurator('#league-editor', '#leagues-json');
});
