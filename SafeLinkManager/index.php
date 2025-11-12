<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Safe Link Manager • Dashboard</title>
  <meta name="referrer" content="no-referrer" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: { 
        extend: { 
          borderRadius: { DEFAULT: '0.3rem' } 
        } 
      }
    };
  </script>
  <style>
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    .spinner { border: 2px solid #f3f4f6; border-top-color: #3b82f6; border-radius: 50%; width: 16px; height: 16px; animation: spin 0.6s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body class="min-h-full bg-gray-50 text-gray-900">
  
  <div class="max-w-5xl mx-auto p-4">
    <!-- Header -->
    <header class="flex items-center justify-between mb-4">
      <h1 class="text-xl font-semibold">
        Safe Link Manager 
        <span class="text-gray-400 text-sm">(PHP Backend)</span>
      </h1>
      <div class="flex items-center gap-2">
        <span id="apiStatus" class="text-xs px-2 py-1 rounded bg-gray-200">Checking...</span>
        <button id="refreshBtn" class="px-2 py-1 text-sm rounded bg-gray-900 text-white">Refresh</button>
      </div>
    </header>

    <!-- Config Bar -->
    <section class="rounded border border-gray-200 bg-white p-3 mb-4">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <label class="text-sm">
          Base URL (redirector)
          <input id="baseUrl" type="url" placeholder="https://brand.tld" class="mt-1 w-full rounded border-gray-300" />
        </label>
        <label class="text-sm">
          System Mode
          <select id="systemOn" class="mt-1 w-full rounded border-gray-300">
            <option value="1">ON</option>
            <option value="0">OFF</option>
          </select>
        </label>
        <label class="text-sm">
          Allowed Countries (CSV ISO2)
          <input id="allowedCsv" placeholder="us,id,sg" class="mt-1 w-full rounded border-gray-300 mono" />
        </label>
        <label class="text-sm">
          Dev Secret (hex 64)
          <div class="flex gap-2 mt-1">
            <input id="secretHex" type="password" placeholder="leave empty for server default" class="w-full rounded border-gray-300 mono" />
            <button id="revealSecret" class="px-2 border rounded">👁️</button>
          </div>
        </label>
        <fieldset class="text-sm md:col-span-2">
          <div class="font-medium mb-1">Rule Flags</div>
          <label class="inline-flex items-center gap-2 mr-4">
            <input id="flagWap" type="checkbox" class="rounded" checked /> WAP required
          </label>
          <label class="inline-flex items-center gap-2 mr-4">
            <input id="flagVpn" type="checkbox" class="rounded" checked /> VPN must be false
          </label>
          <label class="inline-flex items-center gap-2 mr-4">
            <input id="flagProxy" type="checkbox" class="rounded" checked /> Proxy must be false
          </label>
          <label class="inline-flex items-center gap-2">
            <input id="flagBot" type="checkbox" class="rounded" checked /> Bot must be false
          </label>
        </fieldset>
      </div>
    </section>

    <!-- Tabs -->
    <div class="flex gap-2 mb-3 text-sm flex-wrap">
      <button data-tab="routes" class="tab px-3 py-1 rounded bg-gray-900 text-white">Routes</button>
      <button data-tab="signed" class="tab px-3 py-1 rounded bg-gray-200">Signed Link</button>
      <button data-tab="test" class="tab px-3 py-1 rounded bg-gray-200">Test</button>
      <button data-tab="ab" class="tab px-3 py-1 rounded bg-gray-200">A/B</button>
      <button data-tab="preview" class="tab px-3 py-1 rounded bg-gray-200">Preview</button>
      <button data-tab="export" class="tab px-3 py-1 rounded bg-gray-200">Import/Export</button>
      <button data-tab="docs" class="tab px-3 py-1 rounded bg-gray-200">Docs</button>
    </div>

    <!-- Routes Tab -->
    <section id="tab-routes" class="tabpane rounded border border-gray-200 bg-white p-3">
      <div class="flex items-center justify-between mb-2">
        <h2 class="font-medium">Routes <span class="text-gray-400 text-sm">/r/{slug} → URL</span></h2>
        <div class="flex gap-2">
          <button id="addRoute" class="px-2 py-1 rounded bg-gray-900 text-white">Add</button>
          <button id="saveRoutes" class="px-2 py-1 rounded bg-blue-600 text-white">Save All</button>
          <button id="clearRoutes" class="px-2 py-1 rounded border">Clear All</button>
        </div>
      </div>
      <div id="routesContainer" class="space-y-2"></div>
      <div id="routesLoading" class="text-center py-4 text-gray-500">
        <div class="spinner mx-auto mb-2"></div>
        Loading routes...
      </div>
    </section>

    <!-- Signed Link Tab -->
    <section id="tab-signed" class="tabpane hidden rounded border border-gray-200 bg-white p-3">
      <h2 class="font-medium mb-2">Generator Signed Link</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <label class="text-sm">
          Target URL
          <input id="toUrl" type="url" placeholder="https://example.com/landing" class="mt-1 w-full rounded border-gray-300" />
        </label>
        <label class="text-sm">
          Expiration (minutes)
          <input id="expMin" type="number" value="10" min="1" class="mt-1 w-full rounded border-gray-300" />
        </label>
        <div class="md:col-span-2 flex items-end gap-2">
          <button id="btnSign" class="px-3 py-2 rounded bg-gray-900 text-white">Generate Signed URL</button>
          <span class="text-xs text-gray-500">Signed on server for production security.</span>
        </div>
        <div class="md:col-span-2">
          <label class="text-sm">Result</label>
          <input id="signedOut" readonly class="mt-1 w-full rounded border-gray-300 mono" />
          <div class="mt-2 flex gap-2">
            <button data-copy="#signedOut" class="copy px-2 py-1 rounded border">Copy</button>
            <a id="openSigned" target="_blank" class="px-2 py-1 rounded border" href="#">Open</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Test Compose Tab -->
    <section id="tab-test" class="tabpane hidden rounded border border-gray-200 bg-white p-3">
      <h2 class="font-medium mb-3">Test Compose</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <label class="text-sm">
          Mode
          <select id="testMode" class="mt-1 w-full rounded border-gray-300">
            <option value="slug">/r/{slug}</option>
            <option value="signed">/go (signed)</option>
          </select>
        </label>
        <label class="text-sm">
          Slug
          <input id="testSlug" placeholder="promo" class="mt-1 w-full rounded border-gray-300" />
        </label>
        <label class="text-sm">
          Target URL (for signed)
          <input id="testTo" type="url" placeholder="https://example.com" class="mt-1 w-full rounded border-gray-300" />
        </label>
        <label class="text-sm">
          Expiration (minutes)
          <input id="testExp" type="number" value="5" class="mt-1 w-full rounded border-gray-300" />
        </label>
      </div>
      <div class="mt-3 flex gap-2">
        <button id="btnCompose" class="px-3 py-2 rounded bg-gray-900 text-white">Compose</button>
        <input id="composeOut" readonly class="flex-1 rounded border-gray-300 mono" />
        <button data-copy="#composeOut" class="copy px-2 py-1 rounded border">Copy</button>
        <a id="openComposed" target="_blank" class="px-2 py-1 rounded border" href="#">Open</a>
      </div>
    </section>

    <!-- A/B Tab -->
    <section id="tab-ab" class="tabpane hidden rounded border border-gray-200 bg-white p-3">
      <h2 class="font-medium mb-2">A/B Testing (Signed Pair)</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <label class="text-sm">
          URL A
          <input id="abUrlA" type="url" placeholder="https://example.com/variantA" class="mt-1 w-full rounded border-gray-300 mono" />
        </label>
        <label class="text-sm">
          URL B
          <input id="abUrlB" type="url" placeholder="https://example.com/variantB" class="mt-1 w-full rounded border-gray-300 mono" />
        </label>
        <label class="text-sm">
          Weight A (%)
          <input id="abWeightA" type="number" min="0" max="100" value="50" class="mt-1 w-full rounded border-gray-300" />
        </label>
        <label class="text-sm">
          Expiration (minutes)
          <input id="abTtlMin" type="number" min="1" value="10" class="mt-1 w-full rounded border-gray-300" />
        </label>
        <div class="md:col-span-2 flex items-end gap-2">
          <button id="btnGenAB" class="px-3 py-2 rounded bg-gray-900 text-white">Generate Signed Pair</button>
        </div>
        <div>
          <label class="text-sm">Link A</label>
          <input id="abAOut" readonly class="mt-1 w-full rounded border-gray-300 mono" />
          <div class="mt-2 flex gap-2">
            <button data-copy="#abAOut" class="copy px-2 py-1 rounded border">Copy</button>
            <a id="openABA" target="_blank" class="px-2 py-1 rounded border" href="#">Open</a>
          </div>
        </div>
        <div>
          <label class="text-sm">Link B</label>
          <input id="abBOut" readonly class="mt-1 w-full rounded border-gray-300 mono" />
          <div class="mt-2 flex gap-2">
            <button data-copy="#abBOut" class="copy px-2 py-1 rounded border">Copy</button>
            <a id="openABB" target="_blank" class="px-2 py-1 rounded border" href="#">Open</a>
          </div>
        </div>
      </div>
      <hr class="my-3" />
      <h3 class="font-medium mb-2">Simulate Variant Selection</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <label class="text-sm">
          Visitor Key
          <input id="abVisitorKey" class="mt-1 w-full rounded border-gray-300 mono" placeholder="visitor-123" />
        </label>
        <div class="md:col-span-2 flex items-end gap-2">
          <button id="btnSimAB" class="px-3 py-2 rounded bg-gray-900 text-white">Simulate</button>
          <input id="abPickOut" readonly class="flex-1 rounded border-gray-300 mono" />
        </div>
      </div>
    </section>

    <!-- Preview Tab -->
    <section id="tab-preview" class="tabpane hidden rounded border border-gray-200 bg-white p-3">
      <h2 class="font-medium mb-2">Rule Preview & Simulator</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="text-sm">
            Country (ISO2)
            <input id="simCountry" placeholder="id" class="mt-1 w-full rounded border-gray-300 mono" />
          </label>
          <label class="text-sm block mt-2">
            User-Agent
            <textarea id="simUA" rows="3" class="mt-1 w-full rounded border-gray-300 mono" placeholder="Mozilla/5.0 ..."></textarea>
          </label>
          <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
            <label class="inline-flex items-center gap-2"><input id="simMobile" type="checkbox" class="rounded" /> WAP/mobile</label>
            <label class="inline-flex items-center gap-2"><input id="simVpn" type="checkbox" class="rounded" /> VPN</label>
            <label class="inline-flex items-center gap-2"><input id="simProxy" type="checkbox" class="rounded" /> Proxy</label>
            <label class="inline-flex items-center gap-2"><input id="simBot" type="checkbox" class="rounded" /> Bot</label>
          </div>
          <div class="mt-2 flex gap-2">
            <button id="btnDetectUA" class="px-2 py-1 rounded border">Detect mobile</button>
            <button id="btnEval" class="px-3 py-1 rounded bg-gray-900 text-white">Evaluate</button>
          </div>
        </div>
        <div>
          <div class="rounded border p-3">
            <div class="text-sm text-gray-600 mb-2">Result:</div>
            <div id="simDecision" class="text-base font-semibold mb-2">Waiting...</div>
            <ul id="simReasons" class="text-sm list-disc ml-5"></ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Import/Export Tab -->
    <section id="tab-export" class="tabpane hidden rounded border border-gray-200 bg-white p-3">
      <h2 class="font-medium mb-2">Import / Export</h2>
      <div class="flex items-center gap-2 mb-2">
        <input id="routesJson" class="flex-1 rounded border-gray-300 mono" placeholder='{"promo":"https://example.com"}' />
        <button id="btnLoadJson" class="px-2 py-1 rounded border">Load</button>
        <button id="btnExportJson" class="px-2 py-1 rounded border">Download</button>
        <button id="btnCopyJson" class="px-2 py-1 rounded border">Copy</button>
      </div>
      <p class="text-xs text-gray-600">Import/export routes.json. Changes are synced to server when you click "Save All".</p>
    </section>

    <!-- Docs Tab -->
    <section id="tab-docs" class="tabpane hidden rounded border border-gray-200 bg-white p-3">
      <h2 class="font-medium mb-2">Documentation</h2>
      <div class="space-y-3 text-sm">
        <div>
          <div class="font-medium">Architecture</div>
          <ul class="list-disc ml-5">
            <li>Frontend: HTML + Vanilla JS + AJAX</li>
            <li>Backend: PHP modules (config, functions, API)</li>
            <li>Storage: routes.json file</li>
          </ul>
        </div>
        <div>
          <div class="font-medium">API Endpoints</div>
          <ul class="list-disc ml-5 mono text-xs">
            <li>GET /api.php?action=get_routes</li>
            <li>POST /api.php?action=set_route</li>
            <li>DELETE /api.php?action=delete_route</li>
            <li>POST /api.php?action=sign_url</li>
            <li>POST /api.php?action=generate_ab</li>
            <li>POST /api.php?action=evaluate_rules</li>
          </ul>
        </div>
        <div>
          <div class="font-medium">Security</div>
          <ul class="list-disc ml-5">
            <li>HMAC-SHA256 signing happens server-side</li>
            <li>Secret key stored in config.php or environment variable</li>
            <li>CORS protection via allowed origins</li>
            <li>Input sanitization on all endpoints</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 left-1/2 -translate-x-1/2 hidden px-3 py-2 rounded bg-gray-900 text-white text-sm"></div>
  </div>

  <script>
    'use strict';
    
    // API Configuration
    const API_URL = 'api.php';
    
    // Utility Functions
    const $ = (s) => document.querySelector(s);
    const $$ = (s) => document.querySelectorAll(s);
    
    const toast = (m) => {
      const t = $('#toast');
      if (!t) return;
      t.textContent = m;
      t.classList.remove('hidden');
      setTimeout(() => t.classList.add('hidden'), 2000);
    };
    
    const getVal = (sel) => $(sel)?.value || '';
    const setVal = (sel, val) => { const el = $(sel); if (el) el.value = val; };
    const getChecked = (sel) => $(sel)?.checked || false;
    const setChecked = (sel, val) => { const el = $(sel); if (el) el.checked = !!val; };
    
    // API Helper
    async function apiCall(action, data = null, method = 'GET') {
      try {
        const options = {
          method,
          headers: { 'Content-Type': 'application/json' }
        };
        
        let url = `${API_URL}?action=${action}`;
        
        if (data && (method === 'POST' || method === 'DELETE')) {
          options.body = JSON.stringify(data);
        } else if (data && method === 'GET') {
          const params = new URLSearchParams(data);
          url += `&${params}`;
        }
        
        const response = await fetch(url, options);
        const result = await response.json();
        
        if (!result.success) {
          throw new Error(result.error || 'API error');
        }
        
        return result.data;
      } catch (err) {
        toast(`Error: ${err.message}`);
        throw err;
      }
    }
    
    // State Management
    let currentRoutes = {};
    
    function loadState() {
      const saved = localStorage.getItem('slm_ui_state');
      if (saved) {
        try {
          const state = JSON.parse(saved);
          setVal('#baseUrl', state.baseUrl || '');
          setVal('#systemOn', state.systemOn ? '1' : '0');
          setVal('#allowedCsv', state.allowedCsv || '');
          setVal('#secretHex', state.secretHex || '');
          setChecked('#flagWap', state.flagWap !== false);
          setChecked('#flagVpn', state.flagVpn !== false);
          setChecked('#flagProxy', state.flagProxy !== false);
          setChecked('#flagBot', state.flagBot !== false);
        } catch {}
      }
    }
    
    function saveState() {
      const state = {
        baseUrl: getVal('#baseUrl'),
        systemOn: getVal('#systemOn') === '1',
        allowedCsv: getVal('#allowedCsv'),
        secretHex: getVal('#secretHex'),
        flagWap: getChecked('#flagWap'),
        flagVpn: getChecked('#flagVpn'),
        flagProxy: getChecked('#flagProxy'),
        flagBot: getChecked('#flagBot')
      };
      localStorage.setItem('slm_ui_state', JSON.stringify(state));
    }
    
    // Routes Management
    async function loadRoutes() {
      try {
        $('#routesLoading').classList.remove('hidden');
        const routes = await apiCall('get_routes');
        currentRoutes = routes || {};
        renderRoutes();
        $('#routesLoading').classList.add('hidden');
      } catch (err) {
        $('#routesLoading').innerHTML = '<p class="text-red-600">Failed to load routes</p>';
      }
    }
    
    function renderRoutes() {
      const container = $('#routesContainer');
      if (!container) return;
      
      container.innerHTML = '';
      
      const entries = Object.entries(currentRoutes);
      if (entries.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">No routes. Click "Add" to create one.</p>';
        return;
      }
      
      entries.forEach(([slug, url]) => {
        const row = document.createElement('div');
        row.className = 'grid grid-cols-1 md:grid-cols-12 gap-2 items-center p-2 bg-gray-50 rounded';
        row.innerHTML = `
          <div class="md:col-span-3">
            <input class="slug-input w-full rounded border-gray-300 mono text-sm" value="${slug}" data-original="${slug}" />
          </div>
          <div class="md:col-span-6">
            <input class="url-input w-full rounded border-gray-300 mono text-sm" value="${url}" />
          </div>
          <div class="md:col-span-3 flex gap-2">
            <button class="delete-btn px-2 py-1 text-xs rounded border border-red-500 text-red-500 hover:bg-red-50">Delete</button>
          </div>
        `;
        container.appendChild(row);
        
        // Update local state on change
        const slugInput = row.querySelector('.slug-input');
        const urlInput = row.querySelector('.url-input');
        
        slugInput.addEventListener('change', () => {
          const oldSlug = slugInput.dataset.original;
          const newSlug = slugInput.value.trim();
          if (newSlug && newSlug !== oldSlug) {
            delete currentRoutes[oldSlug];
            currentRoutes[newSlug] = urlInput.value;
            slugInput.dataset.original = newSlug;
          }
        });
        
        urlInput.addEventListener('change', () => {
          const slug = slugInput.value.trim();
          if (slug) currentRoutes[slug] = urlInput.value;
        });
        
        // Delete button
        row.querySelector('.delete-btn').addEventListener('click', async () => {
          if (!confirm('Delete this route?')) return;
          try {
            await apiCall('delete_route', { slug }, 'DELETE');
            delete currentRoutes[slug];
            renderRoutes();
            toast('Route deleted');
          } catch {}
        });
      });
    }
    
    async function saveAllRoutes() {
      try {
        // Collect current values from inputs
        const newRoutes = {};
        $$('.slug-input').forEach((input, i) => {
          const slug = input.value.trim();
          const url = $$('.url-input')[i].value.trim();
          if (slug && url) newRoutes[slug] = url;
        });
        
        await apiCall('set_routes', { routes: newRoutes }, 'POST');
        currentRoutes = newRoutes;
        toast('All routes saved');
      } catch {}
    }
    
    // Tab Management
    $$('.tab').forEach(btn => {
      btn.addEventListener('click', () => {
        $$('.tab').forEach(b => {
          b.classList.remove('bg-gray-900', 'text-white');
          b.classList.add('bg-gray-200');
        });
        btn.classList.add('bg-gray-900', 'text-white');
        btn.classList.remove('bg-gray-200');
        
        const tab = btn.dataset.tab;
        $$('.tabpane').forEach(p => p.classList.add('hidden'));
        const pane = $(`#tab-${tab}`);
        if (pane) pane.classList.remove('hidden');
      });
    });
    
    // Event Handlers
    $('#addRoute')?.addEventListener('click', () => {
      currentRoutes[''] = '';
      renderRoutes();
    });
    
    $('#saveRoutes')?.addEventListener('click', saveAllRoutes);
    
    $('#clearRoutes')?.addEventListener('click', async () => {
      if (!confirm('Clear all routes?')) return;
      try {
        await apiCall('clear_routes', null, 'POST');
        currentRoutes = {};
        renderRoutes();
        toast('All routes cleared');
      } catch {}
    });
    
    $('#refreshBtn')?.addEventListener('click', () => location.reload());
    
    $('#revealSecret')?.addEventListener('click', () => {
      const el = $('#secretHex');
      if (el) el.type = el.type === 'password' ? 'text' : 'password';
    });
    
    // Signed URL
    $('#btnSign')?.addEventListener('click', async () => {
      try {
        const data = {
          baseUrl: getVal('#baseUrl'),
          targetUrl: getVal('#toUrl'),
          expirationMinutes: parseInt(getVal('#expMin')) || 10,
          secret: getVal('#secretHex') || null
        };
        const result = await apiCall('sign_url', data, 'POST');
        setVal('#signedOut', result.url);
        $('#openSigned').href = result.url;
        toast('URL signed');
      } catch {}
    });
    
    // Test Compose
    $('#btnCompose')?.addEventListener('click', async () => {
      try {
        const base = getVal('#baseUrl').replace(/\/$/, '');
        const mode = getVal('#testMode');
        let out = '';
        
        if (mode === 'slug') {
          const slug = getVal('#testSlug');
          if (!slug) throw new Error('Slug required');
          out = `${base}/r/${encodeURIComponent(slug)}`;
        } else {
          const data = {
            baseUrl: base,
            targetUrl: getVal('#testTo'),
            expirationMinutes: parseInt(getVal('#testExp')) || 5,
            secret: getVal('#secretHex') || null
          };
          const result = await apiCall('sign_url', data, 'POST');
          out = result.url;
        }
        
        setVal('#composeOut', out);
        $('#openComposed').href = out;
        toast('URL composed');
      } catch (err) {
        toast(err.message);
      }
    });
    
    // A/B Testing
    $('#btnGenAB')?.addEventListener('click', async () => {
      try {
        const data = {
          baseUrl: getVal('#baseUrl'),
          urlA: getVal('#abUrlA'),
          urlB: getVal('#abUrlB'),
          expirationMinutes: parseInt(getVal('#abTtlMin')) || 10,
          secret: getVal('#secretHex') || null
        };
        const result = await apiCall('generate_ab', data, 'POST');
        setVal('#abAOut', result.A);
        setVal('#abBOut', result.B);
        $('#openABA').href = result.A;
        $('#openABB').href = result.B;
        toast('A/B URLs generated');
      } catch {}
    });
    
    $('#btnSimAB')?.addEventListener('click', async () => {
      try {
        const data = {
          visitorKey: getVal('#abVisitorKey'),
          weightA: parseInt(getVal('#abWeightA')) || 50
        };
        const result = await apiCall('simulate_ab', data, 'POST');
        const link = result.variant === 'A' ? getVal('#abAOut') : getVal('#abBOut');
        setVal('#abPickOut', `${result.variant} -> ${link || '(generate first)'}`);
      } catch {}
    });
    
    // Preview/Simulator
    $('#btnDetectUA')?.addEventListener('click', async () => {
      try {
        const data = { userAgent: getVal('#simUA') };
        const result = await apiCall('detect_mobile', data, 'POST');
        setChecked('#simMobile', result.isMobile);
      } catch {}
    });
    
    $('#btnEval')?.addEventListener('click', async () => {
      try {
        const data = {
          systemOn: getVal('#systemOn') === '1',
          allowedCountries: getVal('#allowedCsv').split(',').map(s => s.trim().toLowerCase()).filter(Boolean),
          country: getVal('#simCountry'),
          isMobile: getChecked('#simMobile'),
          isVpn: getChecked('#simVpn'),
          isProxy: getChecked('#simProxy'),
          isBot: getChecked('#simBot'),
          flagWap: getChecked('#flagWap'),
          flagVpn: getChecked('#flagVpn'),
          flagProxy: getChecked('#flagProxy'),
          flagBot: getChecked('#flagBot')
        };
        const result = await apiCall('evaluate_rules', data, 'POST');
        $('#simDecision').textContent = `Decision: ${result.decision}`;
        const ul = $('#simReasons');
        ul.innerHTML = '';
        result.reasons.forEach(r => {
          const li = document.createElement('li');
          li.textContent = r;
          ul.appendChild(li);
        });
      } catch {}
    });
    
    // Import/Export
    $('#btnLoadJson')?.addEventListener('click', () => {
      try {
        const json = getVal('#routesJson');
        const routes = JSON.parse(json);
        if (typeof routes !== 'object') throw new Error('Invalid format');
        currentRoutes = routes;
        renderRoutes();
        toast('Routes loaded from JSON');
      } catch {
        toast('Invalid JSON');
      }
    });
    
    $('#btnExportJson')?.addEventListener('click', () => {
      const json = JSON.stringify(currentRoutes, null, 2);
      setVal('#routesJson', json);
      
      const blob = new Blob([json], { type: 'application/json' });
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = 'routes.json';
      document.body.appendChild(a);
      a.click();
      a.remove();
      toast('Exported');
    });
    
    $('#btnCopyJson')?.addEventListener('click', () => {
      const json = JSON.stringify(currentRoutes);
      navigator.clipboard.writeText(json).then(() => toast('Copied'));
      setVal('#routesJson', json);
    });
    
    // Copy buttons
    document.addEventListener('click', (e) => {
      const copyBtn = e.target.closest('[data-copy]');
      if (!copyBtn) return;
      
      const selector = copyBtn.dataset.copy;
      const el = $(selector);
      if (!el) return;
      
      const text = el.value || el.textContent;
      navigator.clipboard.writeText(text).then(() => toast('Copied'));
    });
    
    // Save state on input
    ['#baseUrl', '#systemOn', '#allowedCsv', '#secretHex'].forEach(sel => {
      $(sel)?.addEventListener('change', saveState);
    });
    ['#flagWap', '#flagVpn', '#flagProxy', '#flagBot'].forEach(sel => {
      $(sel)?.addEventListener('change', saveState);
    });
    
    // API Health Check
    async function checkApiHealth() {
      try {
        const result = await apiCall('health');
        $('#apiStatus').textContent = `✓ API OK (${result.routes_count} routes)`;
        $('#apiStatus').className = 'text-xs px-2 py-1 rounded bg-green-100 text-green-700';
      } catch {
        $('#apiStatus').textContent = '✗ API Error';
        $('#apiStatus').className = 'text-xs px-2 py-1 rounded bg-red-100 text-red-700';
      }
    }
    
    // Initialize
    window.addEventListener('DOMContentLoaded', () => {
      loadState();
      loadRoutes();
      checkApiHealth();
    });
  </script>
</body>
</html>