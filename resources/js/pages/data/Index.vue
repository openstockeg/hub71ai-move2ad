<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Database,
    ExternalLink,
    Link2Off,
    RefreshCw,
    Search,
} from '@lucide/vue';
import { computed } from 'vue';
import { llms, sitemap } from '@/routes';
import answerRoutes from '@/routes/answers';

type Fact = {
    id: string;
    category: string;
    claim: string;
    source_url: string;
    host: string;
    official: boolean;
    needs_verification: boolean;
};

const props = defineProps<{
    verified_at: string;
    domains: string[];
    facts: Fact[];
    answers: {
        total: number;
        asked: number;
        top: {
            id: string;
            question: string;
            locale: 'en' | 'ar';
            asked_count: number;
        }[];
    };
}>();

const categoryLabels: Record<string, string> = {
    visa: 'Visas & residency',
    work: 'Work & labour law',
    scam: 'Scams & offer checks',
    demand: 'Jobs & demand',
    community: 'Community',
    government: 'Government services',
    housing: 'Housing & rent',
    health: 'Health',
    education: 'Schools',
    banking: 'Banking',
    founder: 'Founders',
};

const groups = computed(() => {
    const byCategory = new Map<string, Fact[]>();

    for (const fact of props.facts) {
        byCategory.set(fact.category, [
            ...(byCategory.get(fact.category) ?? []),
            fact,
        ]);
    }

    return [...byCategory].map(([category, facts]) => ({
        category,
        label: categoryLabels[category] ?? category,
        facts,
    }));
});

const officialCount = computed(
    () => props.facts.filter((fact) => fact.official).length,
);

const stats = computed(() => [
    { value: props.facts.length, label: 'curated facts' },
    { value: officialCount.value, label: 'from government sources' },
    {
        value: props.domains.length,
        label: 'official domains the AI may search',
    },
    { value: props.answers.total, label: 'public answer pages so far' },
]);

const steps = [
    {
        icon: Database,
        title: 'Curated facts in every prompt',
        detail: 'Salary thresholds, visa rules, rent law and scam warnings, each with its source. The model answers from these first.',
    },
    {
        icon: Search,
        title: 'Search limited to official domains',
        detail: 'When a fact is missing or may have changed, the model searches only the government and regulator sites listed below.',
    },
    {
        icon: Link2Off,
        title: 'Made-up links are removed',
        detail: 'Any link that is not a curated fact or a real search result is stripped before the page is shown.',
    },
];

const verifiedOn = new Date(props.verified_at).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});
</script>

<template>
    <Head title="Our data" />

    <section class="bg-sand">
        <div class="mx-auto grid max-w-5xl gap-6 px-4 py-8 md:py-14">
            <div class="max-w-3xl">
                <p
                    class="flex items-center gap-2 text-xs font-medium tracking-wide text-brand uppercase md:text-sm"
                >
                    <Database class="size-4" /> Our data
                </p>
                <h1
                    class="mt-3 text-3xl leading-tight font-semibold tracking-tight md:text-5xl"
                >
                    Every answer starts from checked facts, not the open web
                </h1>
                <p
                    class="mt-3 text-base text-muted-foreground md:mt-4 md:text-lg"
                >
                    People moving to Abu Dhabi ask Google and ChatGPT, and get
                    content farms and scammers. Move2AD answers from a curated
                    dataset of Abu Dhabi and UAE facts, last checked
                    {{ verifiedOn }}, and cites the source for every claim.
                </p>
            </div>

            <dl class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="flex flex-col rounded-xl border bg-card p-4 shadow-sm"
                >
                    <dt class="text-sm text-muted-foreground">
                        {{ stat.label }}
                    </dt>
                    <dd
                        class="order-first text-3xl font-semibold text-brand tabular-nums"
                    >
                        {{ stat.value }}
                    </dd>
                </div>
            </dl>
        </div>
    </section>

    <div class="mx-auto grid max-w-5xl gap-10 px-4 py-8 md:py-12">
        <section>
            <h2 class="text-xl font-semibold">How answers stay grounded</h2>
            <ol class="mt-4 grid gap-3 md:grid-cols-3">
                <li
                    v-for="(step, index) in steps"
                    :key="step.title"
                    class="rounded-xl border p-4"
                >
                    <p class="flex items-center gap-2 font-medium">
                        <span
                            class="flex size-6 shrink-0 items-center justify-center rounded-full bg-brand/10 text-xs text-brand"
                            >{{ index + 1 }}</span
                        >
                        {{ step.title }}
                    </p>
                    <p class="mt-2 text-sm text-muted-foreground">
                        {{ step.detail }}
                    </p>
                </li>
            </ol>
        </section>

        <section>
            <h2 class="text-xl font-semibold">The facts</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                <BadgeCheck class="inline size-4 text-brand" /> Official =
                government or regulator page. Others are reputable press or
                industry sources citing official data.
                <RefreshCw class="inline size-3.5" /> = re-checked live on
                official sites before it is stated as definitive.
            </p>

            <div class="mt-5 grid gap-6">
                <div v-for="group in groups" :key="group.category">
                    <h3
                        class="text-sm font-medium tracking-wide text-muted-foreground uppercase"
                    >
                        {{ group.label }}
                        <span class="font-normal"
                            >· {{ group.facts.length }}</span
                        >
                    </h3>
                    <ul class="mt-2 divide-y rounded-xl border">
                        <li
                            v-for="fact in group.facts"
                            :key="fact.id"
                            class="grid gap-2 p-4 text-sm"
                        >
                            <p>{{ fact.claim }}</p>
                            <div
                                class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs"
                            >
                                <a
                                    :href="fact.source_url"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex min-w-0 items-center gap-1 rounded-md px-2 py-0.5"
                                    :class="
                                        fact.official
                                            ? 'bg-brand/10 text-brand'
                                            : 'bg-muted text-muted-foreground'
                                    "
                                >
                                    <BadgeCheck
                                        v-if="fact.official"
                                        class="size-3.5 shrink-0"
                                    />
                                    <ExternalLink
                                        v-else
                                        class="size-3.5 shrink-0"
                                    />
                                    <span class="truncate">{{
                                        fact.host
                                    }}</span>
                                </a>
                                <span
                                    v-if="fact.needs_verification"
                                    class="flex items-center gap-1 text-muted-foreground"
                                    ><RefreshCw class="size-3.5" /> re-checked
                                    live</span
                                >
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section>
            <h2 class="text-xl font-semibold">
                Official domains the AI may search
            </h2>
            <ul class="mt-4 flex flex-wrap gap-2">
                <li
                    v-for="domain in domains"
                    :key="domain"
                    class="rounded-md border px-2.5 py-1 font-mono text-xs"
                >
                    {{ domain }}
                </li>
            </ul>
        </section>

        <section v-if="answers.top.length">
            <h2 class="text-xl font-semibold">Public answer pages</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                Every question becomes a findable, sourced page —
                {{ answers.total }} so far, asked {{ answers.asked }}
                {{ answers.asked === 1 ? 'time' : 'times' }}. Listed in our
                <a :href="sitemap().url" class="underline">sitemap</a> and
                <a :href="llms().url" class="underline">llms.txt</a> so search
                engines and AI assistants can cite them.
            </p>
            <ul class="mt-4 grid gap-2 md:grid-cols-2">
                <li v-for="answer in answers.top" :key="answer.id">
                    <Link
                        :href="answerRoutes.show(answer.id)"
                        class="flex h-full items-start justify-between gap-3 rounded-xl border p-3 text-sm hover:border-brand"
                    >
                        <span
                            :dir="answer.locale === 'ar' ? 'rtl' : 'ltr'"
                            :lang="answer.locale"
                            class="min-w-0"
                            >{{ answer.question }}</span
                        >
                        <span
                            class="shrink-0 text-xs text-muted-foreground tabular-nums"
                            >×{{ answer.asked_count }}</span
                        >
                    </Link>
                </li>
            </ul>
        </section>
    </div>
</template>
