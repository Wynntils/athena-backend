import http from 'k6/http';
import { check } from 'k6';

const BASE = __ENV.BASE_URL || 'http://127.0.0.1:8000/';
const isPrereleaseTag = (v) => /(alpha|beta)/i.test(v || '');

const cases = [
    // FABRIC
    {
        name: 'stable fabric latest -> release',
        stream: 'latest',
        ua: 'Wynntils Artemis\\v2.4.10+MC-1.21.11 (client) FABRIC',
        expect: (v) => !isPrereleaseTag(v),
        assertNeverPrerelease: true,
    },
    {
        name: 'stable fabric ce -> release (default)',
        stream: 'ce',
        ua: 'Wynntils Artemis\\v2.4.10+MC-1.21.11 (client) FABRIC',
        expect: (v) => !isPrereleaseTag(v),
    },
    {
        name: 'beta fabric latest -> beta',
        stream: 'latest',
        ua: 'Wynntils Artemis\\v2.4.10-beta.71+MC-1.21.11 (client) FABRIC',
        expect: (v) => /beta/i.test(v),
    },
    {
        name: 'beta fabric ce -> beta',
        stream: 'ce',
        ua: 'Wynntils Artemis\\v2.4.10-beta.71+MC-1.21.11 (client) FABRIC',
        expect: (v) => /beta/i.test(v),
    },

    // FORGE (should map to NEOFORGE)
    {
        name: 'stable forge latest -> release',
        stream: 'latest',
        ua: 'Wynntils Artemis\\v2.4.10+MC-1.21.11 (client) FORGE',
        expect: (v) => !isPrereleaseTag(v),
    },
    {
        name: 'beta forge latest -> beta',
        stream: 'latest',
        ua: 'Wynntils Artemis\\v2.4.10-beta.71+MC-1.21.11 (client) FORGE',
        expect: (v) => /beta/i.test(v),
    },

    // NEOFORGE (direct)
    {
        name: 'stable neoforge latest -> release',
        stream: 'latest',
        ua: 'Wynntils Artemis\\v2.4.10+MC-1.21.11 (client) NEOFORGE',
        expect: (v) => !isPrereleaseTag(v),
    },
    {
        name: 'beta neoforge latest -> beta',
        stream: 'latest',
        ua: 'Wynntils Artemis\\v2.4.10-beta.71+MC-1.21.11 (client) NEOFORGE',
        expect: (v) => /beta/i.test(v),
    },

    // Explicit stream wins
    {
        name: 'stable neoforge explicit beta -> beta',
        stream: 'beta',
        ua: 'Wynntils Artemis\\v2.4.10+MC-1.21.11 (client) NEOFORGE',
        expect: (v) => /beta/i.test(v),
    },
];


export const options = {
    scenarios: {
        stream_matrix: {
            executor: 'shared-iterations',
            vus: 2,
            iterations: 100,
            maxDuration: '5m',
        },
    },
};

export default function () {
    // NOSONAR
    const c = cases[Math.floor(Math.random() * cases.length)];
    const res = http.get(`${BASE}/version/latest/${c.stream}`, {
        headers: { 'User-Agent': c.ua },
    });

    const checks = {
        [`${c.name}: status 200`]: (r) => r.status === 200,
        [`${c.name}: has version`]: (r) => {
            try { return !!JSON.parse(r.body).version; } catch { return false; }
        },
        [`${c.name}: stream match`]: (r) => {
            try {
                const body = JSON.parse(r.body);
                return typeof body.version === 'string' && c.expect(body.version);
            } catch {
                return false;
            }
        },
    };

    if (c.assertNeverPrerelease) {
        checks[`${c.name}: release never returns prerelease`] = (r) => {
            try {
                const body = JSON.parse(r.body);
                return typeof body.version === 'string' && !isPrereleaseTag(body.version);
            } catch {
                return false;
            }
        };
    }

    const ok = check(res, checks);

    if (!ok) {
        console.log(`FAIL ${c.name} -> ${res.status} ${res.body}`);
    }
}
