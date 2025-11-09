<?php

/**
 * PolyFengshui API & GraphQL Explorer
 *
 * This lightweight page helps developers test the REST and GraphQL endpoints
 * exposed by the PolyFengshui module.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PolyFengshui Explorer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f7f7f7; color: #1f2933; }
        h1 { margin-bottom: 0.25rem; }
        h2 { margin-top: 2rem; }
        p { max-width: 720px; }
        .card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 18px; box-shadow: 0 10px 30px rgba(31, 41, 51, 0.08); }
        label { display: block; font-weight: bold; margin-bottom: 6px; }
        input, textarea { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px; }
        button { background: #2563eb; color: #fff; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-size: 15px; }
        button:hover { background: #1d4ed8; }
        pre { background: #0f172a; color: #f8fafc; padding: 16px; border-radius: 10px; overflow-x: auto; }
        .note { font-size: 13px; color: #6b7280; margin-top: -4px; margin-bottom: 12px; }
        .flex { display: flex; gap: 24px; flex-wrap: wrap; align-items: flex-start; }
        .flex .card { flex: 1; min-width: 320px; }
    </style>
</head>
<body>
    <h1>PolyFengshui Explorer</h1>
    <p>Use the tools below to interact with the REST endpoints and GraphQL queries exposed by the PolyFengshui module.</p>
    <p class="note">
        REST routes are auto-loaded from <code>modules/Polyx/PolyFengshui/routes/api.php</code>
        following the PolyCMS module conventions, so placing the file in that location is enough.
    </p>

    <section class="card">
        <h2>REST Playground</h2>
        <div class="flex">
            <div class="card">
                <h3>GET /api/v1/polyfengshui/date</h3>
                <p class="note">Comma-separated list of years (1000 - 2199). Example: <code>1990,2000,2024</code></p>
                <label for="date-years">years</label>
                <input id="date-years" type="text" value="1990,2000,2024">
                <label for="date-token">Authorization (optional bearer token)</label>
                <input id="date-token" type="text" placeholder="Bearer &lt;token&gt;">
                <button onclick="callRest('date')">Send Request</button>
            </div>

            <div class="card">
                <h3>GET /api/v1/polyfengshui/moving-date</h3>
                <p class="note">Optional date in format YYYY-MM-DD. Defaults to today when empty.</p>
                <label for="moving-date">date</label>
                <input id="moving-date" type="text" placeholder="2025-01-01">
                <label for="moving-token">Authorization (optional bearer token)</label>
                <input id="moving-token" type="text" placeholder="Bearer &lt;token&gt;">
                <button onclick="callRest('moving-date')">Send Request</button>
            </div>

            <div class="card">
                <h3>GET /api/v1/polyfengshui/moving-date-lookup</h3>
                <p class="note">Provide the birth year and optional target date details.</p>
                <label for="lookup-checked">checked_year</label>
                <input id="lookup-checked" type="number" value="1990" min="1000" max="2199">
                <label for="lookup-year">target_year</label>
                <input id="lookup-year" type="number" placeholder="2025" min="1000" max="2199">
                <label for="lookup-month">target_month</label>
                <input id="lookup-month" type="number" placeholder="7" min="1" max="12">
                <label for="lookup-day">target_day</label>
                <input id="lookup-day" type="number" placeholder="15" min="1" max="31">
                <label for="lookup-token">Authorization (optional bearer token)</label>
                <input id="lookup-token" type="text" placeholder="Bearer &lt;token&gt;">
                <button onclick="callRest('moving-date-lookup')">Send Request</button>
            </div>
        </div>
        <h3>Response</h3>
        <pre id="rest-response">Awaiting request...</pre>
    </section>

    <section class="card">
        <h2>GraphQL Playground</h2>
        <p>Submit a query directly to <code>/graphql.json</code>. The example below fetches the same data as the REST endpoints.</p>
        <p class="note">
            PolyCMS automatically loads any schema files located at <code>modules/&lt;Vendor&gt;/&lt;Module&gt;/graphql/*.graphql</code>.
            When creating new modules, simply drop the schema files there and declare custom scalars in the schema
            (for example <code>scalar JSON @scalar(class: "MLL\\GraphQLScalars\\JSON")</code>) so Lighthouse can resolve them.
        </p>
        <label for="graphql-query">Query</label>
        <textarea id="graphql-query" rows="12">query Example {
  fengshuiDate(years: [1990, 2000, 2024]) {
    good_bad
    good_zodiac_by_year
    bad_zodiac_by_year
  }

  fengshuiMovingDate(date: "2025-01-01")

  fengshuiMovingDateLookup(
    checked_year: 1990
    target_year: 2025
    target_month: 7
    target_day: 15
  ) {
    good_bad
    good_zodiac_by_year
    bad_zodiac_by_year
  }
}</textarea>
        <label for="graphql-token">Authorization (optional bearer token)</label>
        <input id="graphql-token" type="text" placeholder="Bearer &lt;token&gt;">
        <button onclick="callGraphQL()">Run Query</button>
        <p class="note">
            Responses like <code>good_bad</code> are returned as a JSON scalar. GraphQL will always return the full object,
            so pick the attribute you need on the client (e.g. <code>good_bad.now</code> or <code>good_zodiac_by_year[0]</code>) or
            extend the schema with typed fields if you prefer field-level selection.
        </p>
        <pre class="bg-gray-900 text-gray-100 rounded p-4 overflow-x-auto text-xs">
// Example: query and extract "now" from good_bad
fetch(`${origin}/graphql.json`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    body: JSON.stringify({
        query: `query ($years: [Int!]!) {
            fengshuiDate(years: $years) { good_bad }
        }`,
        variables: { years: [1990, 2000, 2024] }
    })
})
    .then(res => res.json())
    .then(({ data }) => {
        const goodBad = data?.fengshuiDate?.good_bad; // JSON object
        const now = goodBad?.now; // { solar_terms, solar, lunar }
        console.log('Current solar month:', now?.solar?.month);
    });
        </pre>
        <h3>Response</h3>
        <pre id="graphql-response">Awaiting query...</pre>
    </section>

    <script>
        const origin = window.location.origin || '';

        async function callRest(endpoint) {
            const baseUrl = origin + '/api/v1/polyfengshui/';
            const responseBox = document.getElementById('rest-response');
            const headers = { 'Accept': 'application/json' };

            let query = '';

            if (endpoint === 'date') {
                const years = document.getElementById('date-years').value;
                const token = document.getElementById('date-token').value;
                if (token) headers['Authorization'] = token;
                query = '?years=' + encodeURIComponent(years);
            }

            if (endpoint === 'moving-date') {
                const date = document.getElementById('moving-date').value;
                const token = document.getElementById('moving-token').value;
                if (token) headers['Authorization'] = token;
                if (date) query = '?date=' + encodeURIComponent(date);
            }

            if (endpoint === 'moving-date-lookup') {
                const checked = document.getElementById('lookup-checked').value;
                const year = document.getElementById('lookup-year').value;
                const month = document.getElementById('lookup-month').value;
                const day = document.getElementById('lookup-day').value;
                const token = document.getElementById('lookup-token').value;
                if (token) headers['Authorization'] = token;

                const params = new URLSearchParams();
                if (checked) params.append('checked_year', checked);
                if (year) params.append('target_year', year);
                if (month) params.append('target_month', month);
                if (day) params.append('target_day', day);
                query = '?' + params.toString();
            }

            responseBox.textContent = 'Loading...';

            try {
                const res = await fetch(baseUrl + endpoint + query, { headers });
                const json = await res.json();
                responseBox.textContent = JSON.stringify(json, null, 2);
            } catch (error) {
                responseBox.textContent = 'Error: ' + error.message;
            }
        }

        async function callGraphQL() {
            const responseBox = document.getElementById('graphql-response');
            const query = document.getElementById('graphql-query').value;
            const token = document.getElementById('graphql-token').value;

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            if (token) {
                headers['Authorization'] = token;
            }

            responseBox.textContent = 'Loading...';

            try {
                const res = await fetch(origin + '/graphql.json', {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({ query })
                });
                const json = await res.json();
                responseBox.textContent = JSON.stringify(json, null, 2);
            } catch (error) {
                responseBox.textContent = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>

