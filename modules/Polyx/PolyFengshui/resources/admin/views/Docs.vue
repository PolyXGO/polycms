<template>
    <div class="space-y-8">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-50">PolyFengshui API &amp; GraphQL Docs</h1>
            <p class="text-sm text-gray-500 dark:text-gray-300">
                Overview of REST and GraphQL endpoints plus token integration for the PolyFengshui module.
            </p>
            <div class="bg-blue-50 text-blue-700 border border-blue-100 dark:bg-blue-900/40 dark:border-blue-900 dark:text-blue-200 rounded-md p-4 text-xs space-y-1">
                <p><strong>Current domain:</strong> <code>{{ origin }}</code></p>
                <p><strong>REST base:</strong> <code>{{ apiBase }}</code></p>
                <p><strong>Admin API:</strong> <code>{{ adminBase }}</code></p>
                <p><strong>GraphQL endpoint:</strong> <code>{{ graphqlEndpoint }}</code></p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-lg shadow divide-y divide-gray-100 dark:divide-gray-800">
            <section class="p-6 space-y-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Security &amp; tokens</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Enable <strong>Token required</strong> to force clients to send the header
                    <code>Authorization: Bearer &lt;token&gt;</code> on every request. Tokens can be domain-restricted and managed from the admin page.
                </p>
            </section>

            <section class="p-6 space-y-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">REST API</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Module REST routes are auto-loaded from <code>modules/Polyx/PolyFengshui/routes/api.php</code> (and the same pattern applies to other modules),
                    so simply placing a routes file there is enough for the endpoints to work.
                </p>
                <div class="space-y-4 text-sm text-gray-700 dark:text-gray-200">
                    <div>
                        <p class="font-semibold">GET /api/v1/polyfengshui/date</p>
                        <p>Returns Can-Chi data and good/bad dates for the provided years.</p>
                        <pre class="bg-gray-900 text-gray-100 rounded p-4 mt-2 overflow-x-auto text-xs">
Query:
GET {{ apiBase }}/date?years=1990,2000,2024

Response:
{
    "good_bad": {...},
    "good_zodiac_by_year": [...],
    "bad_zodiac_by_year": {
        "tu_hanh_xung": [...],
        "thien_can_khac": [...]
    }
}</pre>
                    </div>
                    <div>
                        <p class="font-semibold">GET /api/v1/polyfengshui/moving-date</p>
                        <p>Suggests auspicious moving dates based on today or a specific date.</p>
                        <pre class="bg-gray-900 text-gray-100 rounded p-4 mt-2 overflow-x-auto text-xs">
 GET {{ apiBase }}/moving-date?date=2025-01-01</pre>
                    </div>
                    <div>
                        <p class="font-semibold">GET /api/v1/polyfengshui/moving-date-lookup</p>
                        <p>Looks up moving dates compatible with the target age and optional date parameters.</p>
                        <pre class="bg-gray-900 text-gray-100 rounded p-4 mt-2 overflow-x-auto text-xs">
 GET {{ apiBase }}/moving-date-lookup?checked_year=1990&amp;target_year=2025&amp;target_month=7&amp;target_day=15</pre>
                    </div>
                </div>

                <div class="space-y-4 text-sm text-gray-700 dark:text-gray-200">
                    <p class="font-semibold">Token management endpoints (Admin)</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>GET {{ adminBase }}/tokens</li>
                        <li>POST {{ adminBase }}/tokens</li>
                        <li>DELETE {{ adminBase }}/tokens/&lt;id&gt;</li>
                        <li>POST {{ adminBase }}/tokens/settings</li>
                    </ul>
                </div>
            </section>

            <section class="p-6 space-y-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Sample requests</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Example request to <code>{{ apiBase }}/date</code> including the header <code>Authorization: Bearer &lt;TOKEN&gt;</code>.
                </p>
                <div class="rounded border border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap border-b border-gray-200 dark:border-gray-700">
                        <button
                            v-for="language in sampleTabs"
                            :key="language"
                            class="px-4 py-2 text-xs font-medium transition"
                            :class="language === activeSample ? 'bg-gray-900 text-white dark:bg-gray-200 dark:text-gray-900' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                            @click="activeSample = language"
                        >
                            {{ language }}
                        </button>
                    </div>
                    <pre class="bg-gray-900 text-gray-100 rounded-b p-4 overflow-x-auto text-xs" v-text="sampleCode"></pre>
                </div>
            </section>

            <section class="p-6 space-y-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">GraphQL</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    GraphQL operations are imported via <code>#import ./polyfengshui.graphql</code> and sent to
                    <code>{{ graphqlEndpoint }}</code>.
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    PolyCMS automatically loads any <code>*.graphql</code> dropped into <code>modules/&lt;Vendor&gt;/&lt;Module&gt;/graphql/</code>.
                    Third-party modules can therefore bundle their own schema files and declare custom scalars there
                    (e.g. <code>scalar JSON @scalar(class: "MLL\GraphQLScalars\JSON")</code>) without touching core.
                </p>
                <pre class="bg-gray-900 text-gray-100 rounded p-4 mt-2 overflow-x-auto text-xs">
query Example($years: [Int!]!, $checked: Int!) {
  fengshuiDate(years: $years) {
    good_bad
    good_zodiac_by_year
  }

  fengshuiMovingDate(date: "2025-01-01")

  fengshuiMovingDateLookup(
    checked_year: $checked
    target_year: 2025
    target_month: 7
    target_day: 15
  ) {
    bad_zodiac_by_year
  }
}</pre>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Fields such as <code>good_bad</code> resolve to the <code>JSON</code> scalar, so GraphQL always returns the
                    complete object. If you only need a single attribute (for example <code>good_bad.good</code>), extract it on the
                    client side or expose a typed field in your own schema extension.
                </p>
                <div class="bg-gray-900 text-gray-100 rounded p-4 mb-4 text-xs">
// Example: pull only the "now" portion from the GraphQL response
const { data } = await fetchGraphQL({
  query: `query ($years: [Int!]!, $checked: Int!) {
    fengshuiDate(years: $years) { good_bad }
  }`,
  variables: { years: [1990, 2000, 2024], checked: 1990 },
});

const goodBad = data.fengshuiDate.good_bad; // JSON object
const currentWindow = goodBad.now; // { solar_terms: ..., solar: {...}, lunar: {...} }

// Or a specific value
const currentSolarMonth = goodBad.now?.solar?.month;
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Don't forget to include the <code>Authorization</code> header if token protection is enabled.
                </p>

                <div class="space-y-2 text-sm text-gray-700">
                    <p class="font-semibold">Token management queries and mutations:</p>
                    <pre class="bg-gray-900 text-gray-100 rounded p-4 overflow-x-auto text-xs">
query Tokens {
  polyfengshuiTokens {
    id
    name
    token
    domain
    created_at
  }
  polyfengshuiTokenSettings {
    active
  }
}

mutation CreateToken {
  createPolyfengshuiToken(input: { name: "Partner A", domain: "example.com" }) {
    token { id token }
    tokens { id name }
  }
}

mutation DeleteToken {
  deletePolyfengshuiToken(id: 1) {
    tokens { id name }
  }
}

mutation UpdateSettings {
  updatePolyfengshuiTokenSettings(active: true) {
    active
  }
}</pre>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

const origin = typeof window !== 'undefined' ? window.location.origin : '';
const apiBase = `${origin}/api/v1/polyfengshui`;
const adminBase = `${origin}/admin-api/polyfengshui`;
const graphqlEndpoint = `${origin}/graphql.json`;

const samples: Record<string, string> = {
    PHP: `<?php
$client = new \\GuzzleHttp\\Client();
$response = $client->get('${apiBase}/date', [
    'query' => ['years' => '1990,2000,2024'],
    'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer YOUR_TOKEN',
    ],
]);
echo $response->getBody();`,
    curl: `curl -X GET "${apiBase}/date?years=1990,2000,2024" \\
  -H "Accept: application/json" \\
  -H "Authorization: Bearer YOUR_TOKEN"`,
    'C#': `using var client = new HttpClient();
client.DefaultRequestHeaders.Add("Accept", "application/json");
client.DefaultRequestHeaders.Add("Authorization", "Bearer YOUR_TOKEN");
var response = await client.GetAsync("${apiBase}/date?years=1990,2000,2024");
var payload = await response.Content.ReadAsStringAsync();`,
    Python: `import requests

headers = {
    "Accept": "application/json",
    "Authorization": "Bearer YOUR_TOKEN",
}
params = {"years": "1990,2000,2024"}
resp = requests.get("${apiBase}/date", headers=headers, params=params)
print(resp.json())`,
    JavaScript: `fetch("${apiBase}/date?years=1990,2000,2024", {
    headers: {
        "Accept": "application/json",
        "Authorization": "Bearer YOUR_TOKEN",
    },
})
    .then(res => res.json())
    .then(console.log);`,
};

const sampleTabs = Object.keys(samples);
const activeSample = ref(sampleTabs[0]);

const sampleCode = computed(() => samples[activeSample.value] ?? '');
</script>
