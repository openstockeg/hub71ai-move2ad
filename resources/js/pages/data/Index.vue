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
import { data, llms, sitemap } from '@/routes';
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
    locale: 'en' | 'ar';
    verified_at: string;
    links_checked_at: string;
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

const labels = {
    en: {
        eyebrow: 'Our data',
        title: 'Every answer starts from checked facts, not the open web',
        intro: (verified: string, checked: string) =>
            `People moving to Abu Dhabi ask Google and ChatGPT, and get content farms and scammers. Move2AD answers from a curated dataset of Abu Dhabi and UAE facts, curated on ${verified} with every source link re-checked on ${checked}, and cites the source for every claim.`,
        stats: [
            'curated facts',
            'from government sources',
            'official domains the AI may search',
            'public answer pages so far',
        ],
        groundedTitle: 'How answers stay grounded',
        steps: [
            {
                title: 'Curated facts in every prompt',
                detail: 'Salary thresholds, visa rules, rent law and scam warnings, each with its source. The model answers from these first.',
            },
            {
                title: 'Search limited to official domains',
                detail: 'When a fact is missing or may have changed, the model searches only the government and regulator sites listed below.',
            },
            {
                title: 'Made-up links are removed',
                detail: 'Any link that is not a curated fact or a real search result is stripped before the page is shown.',
            },
        ],
        factsTitle: 'The facts',
        legendOfficial:
            'Official = government or regulator page. Others are reputable press or industry sources citing official data.',
        legendLive:
            '= re-checked live on official sites before it is stated as definitive.',
        factsInEnglish: '',
        live: 're-checked live',
        domainsTitle: 'Official domains the AI may search',
        answersTitle: 'Public answer pages',
        answersIntro: (total: number, asked: number) =>
            `Every question becomes a findable, sourced page — ${total} so far, asked ${asked} ${asked === 1 ? 'time' : 'times'}. Listed in our`,
        answersOutro: 'so search engines and AI assistants can cite them.',
        and: 'and',
        categories: {
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
        } as Record<string, string>,
    },
    ar: {
        eyebrow: 'بياناتنا',
        title: 'كل إجابة تبدأ من حقائق موثّقة، لا من الإنترنت المفتوح',
        intro: (verified: string, checked: string) =>
            `من يفكر في الانتقال إلى أبوظبي يسأل Google وChatGPT، فيجد مواقع محتوى رديئة ومحتالين. يجيب Move2AD من مجموعة بيانات منتقاة عن أبوظبي والإمارات، جُمعت في ${verified} وأُعيد فحص كل روابط مصادرها في ${checked}، ويذكر المصدر لكل معلومة.`,
        stats: [
            'حقيقة منتقاة',
            'من مصادر حكومية',
            'نطاقًا رسميًا يبحث فيه الذكاء الاصطناعي',
            'صفحة إجابة عامة حتى الآن',
        ],
        groundedTitle: 'كيف تبقى الإجابات مستندة إلى مصادر',
        steps: [
            {
                title: 'حقائق منتقاة في كل طلب',
                detail: 'حدود الرواتب وقواعد التأشيرات وقانون الإيجار وتحذيرات الاحتيال، كلٌّ مع مصدره. يجيب النموذج منها أولًا.',
            },
            {
                title: 'بحث محصور في النطاقات الرسمية',
                detail: 'إذا غابت معلومة أو ربما تغيّرت، يبحث النموذج فقط في المواقع الحكومية والتنظيمية المذكورة أدناه.',
            },
            {
                title: 'حذف الروابط المختلَقة',
                detail: 'أي رابط ليس من حقائقنا ولا من نتائج بحث حقيقية يُحذف قبل عرض الصفحة.',
            },
        ],
        factsTitle: 'الحقائق',
        legendOfficial:
            'رسمي = صفحة جهة حكومية أو تنظيمية. والبقية مصادر صحفية أو متخصصة موثوقة تنقل بيانات رسمية.',
        legendLive:
            '= يُعاد التحقق منها مباشرة من المواقع الرسمية قبل اعتمادها.',
        factsInEnglish: 'الحقائق مكتوبة بالإنجليزية كما جُمعت من مصادرها.',
        live: 'يُعاد التحقق منها مباشرة',
        domainsTitle: 'النطاقات الرسمية التي يبحث فيها الذكاء الاصطناعي',
        answersTitle: 'صفحات الإجابات العامة',
        answersIntro: (total: number, asked: number) =>
            `كل سؤال يصبح صفحة موثّقة يمكن العثور عليها — ${total} صفحة حتى الآن، سُئلت ${asked} مرة. مُدرجة في`,
        answersOutro:
            'لتتمكن محركات البحث ومساعدات الذكاء الاصطناعي من الاستشهاد بها.',
        and: 'و',
        categories: {
            visa: 'التأشيرات والإقامة',
            work: 'العمل وقانون العمل',
            scam: 'الاحتيال وفحص العروض',
            demand: 'الوظائف والطلب',
            community: 'المجتمع',
            government: 'الخدمات الحكومية',
            housing: 'السكن والإيجار',
            health: 'الصحة',
            education: 'المدارس',
            banking: 'البنوك',
            founder: 'روّاد الأعمال',
        } as Record<string, string>,
    },
};

const t = computed(() => labels[props.locale]);
const dir = computed(() => (props.locale === 'ar' ? 'rtl' : 'ltr'));

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
        label: t.value.categories[category] ?? category,
        facts,
    }));
});

const stats = computed(() =>
    [
        props.facts.length,
        props.facts.filter((fact) => fact.official).length,
        props.domains.length,
        props.answers.total,
    ].map((value, index) => ({ value, label: t.value.stats[index] })),
);

const stepIcons = [Database, Search, Link2Off];

// Dates are calendar days (YYYY-MM-DD): format in UTC so no timezone shifts them by a day.
const formatDate = (date: string) =>
    new Date(date).toLocaleDateString(
        props.locale === 'ar' ? 'ar-AE' : 'en-GB',
        { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' },
    );
</script>

<template>
    <Head :title="locale === 'ar' ? 'بياناتنا ومصادرنا' : 'Our data'" />

    <div :dir="dir" :lang="locale">
        <section class="bg-sand">
            <div class="mx-auto grid max-w-5xl gap-6 px-4 py-8 md:py-14">
                <div class="flex items-center justify-between gap-3">
                    <p
                        class="flex items-center gap-2 text-xs font-medium tracking-wide text-brand uppercase md:text-sm"
                    >
                        <Database class="size-4" /> {{ t.eyebrow }}
                    </p>
                    <div
                        class="flex gap-1 rounded-md border bg-card p-0.5 text-sm"
                    >
                        <Link
                            v-for="option in [
                                { value: 'en', label: 'English' },
                                { value: 'ar', label: 'العربية' },
                            ]"
                            :key="option.value"
                            :href="data({ query: { lang: option.value } })"
                            preserve-scroll
                            replace
                            class="rounded px-2.5 py-1"
                            :class="
                                locale === option.value
                                    ? 'bg-brand/10 font-medium text-brand'
                                    : 'text-muted-foreground'
                            "
                            >{{ option.label }}</Link
                        >
                    </div>
                </div>
                <div class="max-w-3xl">
                    <h1
                        class="text-3xl leading-tight font-semibold tracking-tight md:text-5xl"
                    >
                        {{ t.title }}
                    </h1>
                    <p
                        class="mt-3 text-base text-muted-foreground md:mt-4 md:text-lg"
                    >
                        {{
                            t.intro(
                                formatDate(verified_at),
                                formatDate(links_checked_at),
                            )
                        }}
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
                <h2 class="text-xl font-semibold">{{ t.groundedTitle }}</h2>
                <ol class="mt-4 grid gap-3 md:grid-cols-3">
                    <li
                        v-for="(step, index) in t.steps"
                        :key="step.title"
                        class="rounded-xl border p-4"
                    >
                        <p class="flex items-center gap-2 font-medium">
                            <span
                                class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brand/10 text-brand"
                            >
                                <component
                                    :is="stepIcons[index]"
                                    class="size-4"
                                />
                            </span>
                            {{ step.title }}
                        </p>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ step.detail }}
                        </p>
                    </li>
                </ol>
            </section>

            <section>
                <h2 class="text-xl font-semibold">{{ t.factsTitle }}</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    <BadgeCheck class="inline size-4 text-brand" />
                    {{ t.legendOfficial }}
                    <RefreshCw class="inline size-3.5" /> {{ t.legendLive }}
                    {{ t.factsInEnglish }}
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
                                <p dir="ltr" lang="en" class="text-start">
                                    {{ fact.claim }}
                                </p>
                                <div
                                    class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs"
                                >
                                    <a
                                        :href="fact.source_url"
                                        target="_blank"
                                        rel="noopener"
                                        dir="ltr"
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
                                        ><RefreshCw class="size-3.5" />
                                        {{ t.live }}</span
                                    >
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-xl font-semibold">{{ t.domainsTitle }}</h2>
                <ul class="mt-4 flex flex-wrap gap-2" dir="ltr">
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
                <h2 class="text-xl font-semibold">{{ t.answersTitle }}</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ t.answersIntro(answers.total, answers.asked) }}
                    <a :href="sitemap().url" class="underline">sitemap</a>
                    {{ t.and }}
                    <a :href="llms().url" class="underline">llms.txt</a>
                    {{ t.answersOutro }}
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
    </div>
</template>
