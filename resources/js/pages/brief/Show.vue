<script setup lang="ts">
import { Head, Link, router, useHttp, usePoll } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Banknote,
    BadgeCheck,
    Compass,
    ExternalLink,
    MessageCircleQuestion,
    Plane,
    Rocket,
    Home,
    Luggage,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import SourceLink from '@/components/SourceLink.vue';
import { Skeleton } from '@/components/ui/skeleton';
import { home } from '@/routes';
import { generate } from '@/routes/briefs';
import type { Brief, Stage } from '@/types/brief';

const props = defineProps<{ brief: Brief }>();

const labels = {
    en: {
        loading: 'Checking official sources for you…',
        loadingSteps: [
            'Reading Golden & Green Visa criteria on added.gov.ae',
            'Checking job-seeker visa rules on icp.gov.ae',
            'Comparing rents and upfront costs',
            'Scanning for scams that target your profile',
            'Writing your brief',
        ],
        failed: 'We could not finish your brief. Please try again.',
        retry: 'Try again',
        fit: 'Fit',
        visas: 'Your visa routes',
        money: 'Money',
        salary: 'Salary',
        rent: '1-bedroom rent / year',
        upfront: 'Upfront costs',
        steps: 'Your path',
        watchOut: 'Watch out',
        questions: 'People like you also ask',
        sources: 'Sources',
        official: 'official',
        years: 'yrs',
        family: {
            single: 'moving alone',
            couple: 'with partner',
            family: 'with kids',
        },
        likelihood: {
            likely: 'Likely',
            possible: 'Possible',
            unlikely: 'Unlikely',
        },
        level: {
            strong: 'Strong fit',
            good: 'Good fit',
            possible: 'Possible fit',
            challenging: 'Challenging',
        },
        stage: {
            explore: 'Explore',
            visit: 'Visit',
            move: 'Move',
            settle: 'Settle',
            build: 'Build',
        },
        newBrief: 'New brief',
    },
    ar: {
        loading: 'نتحقق من المصادر الرسمية من أجلك…',
        loadingSteps: [
            'قراءة شروط الإقامة الذهبية والخضراء',
            'التحقق من تأشيرة البحث عن عمل',
            'مقارنة الإيجارات والتكاليف الأولية',
            'البحث عن عمليات الاحتيال التي تستهدف ملفك',
            'كتابة ملخصك',
        ],
        failed: 'تعذّر إكمال الملخص. يرجى المحاولة مرة أخرى.',
        retry: 'حاول مجددًا',
        fit: 'الملاءمة',
        visas: 'مسارات التأشيرة',
        money: 'المال',
        salary: 'الراتب',
        rent: 'إيجار غرفة نوم واحدة / سنويًا',
        upfront: 'التكاليف الأولية',
        steps: 'مسارك',
        watchOut: 'انتبه',
        questions: 'أسئلة يطرحها أشخاص مثلك',
        sources: 'المصادر',
        official: 'رسمي',
        years: 'سنوات',
        family: { single: 'بمفردك', couple: 'مع الشريك', family: 'مع الأطفال' },
        likelihood: {
            likely: 'مرجّح',
            possible: 'ممكن',
            unlikely: 'غير مرجّح',
        },
        level: {
            strong: 'ملاءمة قوية',
            good: 'ملاءمة جيدة',
            possible: 'ملاءمة ممكنة',
            challenging: 'صعب',
        },
        stage: {
            explore: 'استكشف',
            visit: 'زُر',
            move: 'انتقل',
            settle: 'استقر',
            build: 'ابنِ',
        },
        newBrief: 'ملخص جديد',
    },
} as const;

const t = computed(() => labels[props.brief.locale]);
const c = computed(() => props.brief.content);
const dir = computed(() => (props.brief.locale === 'ar' ? 'rtl' : 'ltr'));

const stageIcons: Record<Stage, typeof Compass> = {
    explore: Compass,
    visit: Plane,
    move: Luggage,
    settle: Home,
    build: Rocket,
};

const likelihoodClass = {
    likely: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',
    possible:
        'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    unlikely: 'bg-muted text-muted-foreground',
};

// Readable path for the sources list, e.g. "/en/visas/golden-visa".
const sourcePath = (url: string) => {
    const { pathname } = new URL(url);

    return pathname === '/'
        ? ''
        : decodeURIComponent(pathname).replace(/\/$/, '');
};

const loading = computed(() =>
    ['pending', 'generating'].includes(props.brief.status),
);

// Kick off generation, then poll as a fallback (e.g. page refreshed mid-generation).
const http = useHttp(generate(props.brief.id), {});
onMounted(() => {
    if (props.brief.status === 'pending') {
        http.submit().finally(() => router.reload({ only: ['brief'] }));
    }
});
const { stop } = usePoll(
    3000,
    { only: ['brief'] },
    { autoStart: loading.value },
);

// Rotate loading messages while waiting.
const loadingStep = ref(0);
const ticker = setInterval(() => {
    loadingStep.value = Math.min(
        loadingStep.value + 1,
        t.value.loadingSteps.length - 1,
    );
}, 6000);
onUnmounted(() => clearInterval(ticker));

watch(
    loading,
    (isLoading) => {
        if (!isLoading) {
            stop();
            clearInterval(ticker);
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head :title="c?.headline ?? `${brief.profession} from ${brief.country}`" />

    <!-- grid-cols-1 (minmax(0, 1fr)) everywhere: long words/URLs must never widen the page, or RTL content spills off-screen. -->
    <div
        :dir="dir"
        :lang="brief.locale"
        class="mx-auto max-w-3xl px-4 py-6 wrap-break-word md:py-12"
    >
        <div
            class="mb-4 flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
        >
            <span class="rounded-full bg-sand px-3 py-1">{{
                brief.profession
            }}</span>
            <span class="rounded-full bg-sand px-3 py-1">{{
                brief.country
            }}</span>
            <span class="rounded-full bg-sand px-3 py-1"
                >{{ brief.experience_years }} {{ t.years }}</span
            >
            <span class="rounded-full bg-sand px-3 py-1">{{
                t.family[brief.family]
            }}</span>
            <Link
                :href="home()"
                class="ms-auto underline underline-offset-4 hover:text-foreground"
                >{{ t.newBrief }}</Link
            >
        </div>

        <!-- Pending -->
        <div v-if="loading" class="grid grid-cols-1 gap-6">
            <div class="rounded-xl border bg-card p-6">
                <p class="flex items-center gap-2 font-medium">
                    <span class="relative flex size-3">
                        <span
                            class="absolute inline-flex size-full animate-ping rounded-full bg-brand opacity-60"
                        />
                        <span
                            class="relative inline-flex size-3 rounded-full bg-brand"
                        />
                    </span>
                    {{ t.loading }}
                </p>
                <ul class="mt-4 grid gap-2 text-sm">
                    <li
                        v-for="(step, i) in t.loadingSteps"
                        :key="step"
                        class="flex items-center gap-2 transition-opacity"
                        :class="i <= loadingStep ? 'opacity-100' : 'opacity-30'"
                    >
                        <BadgeCheck
                            class="size-4"
                            :class="
                                i < loadingStep
                                    ? 'text-brand'
                                    : 'text-muted-foreground'
                            "
                        />
                        {{ step }}
                    </li>
                </ul>
            </div>
            <Skeleton class="h-10 w-3/4" />
            <Skeleton class="h-24 w-full" />
            <Skeleton class="h-40 w-full" />
        </div>

        <!-- Failed -->
        <div
            v-else-if="brief.status === 'failed' || !c"
            class="rounded-xl border border-destructive/40 bg-card p-6"
        >
            <p>{{ t.failed }}</p>
            <Link
                :href="home()"
                class="mt-3 inline-block font-medium text-brand underline underline-offset-4"
                >{{ t.retry }}</Link
            >
        </div>

        <!-- Ready -->
        <article v-else class="grid grid-cols-1 gap-8 md:gap-10">
            <header>
                <h1
                    class="text-2xl leading-snug font-semibold tracking-tight md:text-4xl md:leading-tight"
                >
                    {{ c.headline }}
                </h1>
                <p
                    class="mt-3 text-base text-muted-foreground md:mt-4 md:text-lg"
                >
                    {{ c.summary }}
                </p>
                <div class="mt-5 rounded-xl border bg-sand p-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="rounded-full bg-brand px-3 py-1 text-sm font-semibold text-brand-foreground"
                            >{{ t.level[c.fit.level] }}</span
                        >
                        <SourceLink
                            :url="c.fit.source_url"
                            :sources="brief.sources"
                        />
                    </div>
                    <p class="mt-2 text-sm">{{ c.fit.reason }}</p>
                </div>
            </header>

            <section>
                <h2 class="mb-4 text-xl font-semibold">{{ t.visas }}</h2>
                <div class="grid grid-cols-1 gap-3">
                    <div
                        v-for="visa in c.visa_paths"
                        :key="visa.name"
                        class="rounded-xl border bg-card p-4"
                    >
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold">{{ visa.name }}</h3>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="likelihoodClass[visa.likelihood]"
                                >{{ t.likelihood[visa.likelihood] }}</span
                            >
                            <span class="text-xs text-muted-foreground">{{
                                visa.duration
                            }}</span>
                        </div>
                        <p class="mt-2 text-sm">{{ visa.requirements }}</p>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ visa.why }}
                        </p>
                        <div class="mt-3">
                            <SourceLink
                                :url="visa.source_url"
                                :sources="brief.sources"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
                    <Banknote class="size-5 text-brand" />{{ t.money }}
                </h2>
                <dl
                    class="grid grid-cols-1 gap-4 rounded-xl border bg-card p-4 text-sm"
                    :class="
                        c.money.salary_range
                            ? 'md:grid-cols-3'
                            : 'md:grid-cols-2'
                    "
                >
                    <div v-if="c.money.salary_range">
                        <dt class="text-xs text-muted-foreground uppercase">
                            {{ t.salary }}
                        </dt>
                        <dd class="mt-1">{{ c.money.salary_range }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground uppercase">
                            {{ t.rent }}
                        </dt>
                        <dd class="mt-1">{{ c.money.rent_1br }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground uppercase">
                            {{ t.upfront }}
                        </dt>
                        <dd class="mt-1">{{ c.money.upfront_costs }}</dd>
                    </div>
                    <p class="text-muted-foreground md:col-span-full">
                        {{ c.money.note }}
                    </p>
                    <div class="md:col-span-full">
                        <SourceLink
                            :url="c.money.source_url"
                            :sources="brief.sources"
                        />
                    </div>
                </dl>
            </section>

            <section>
                <h2 class="mb-4 text-xl font-semibold">{{ t.steps }}</h2>
                <ol class="relative grid gap-5 border-s ps-6">
                    <li v-for="(step, i) in c.steps" :key="i" class="relative">
                        <span
                            class="absolute -start-[37px] flex size-6 items-center justify-center rounded-full bg-brand text-brand-foreground"
                        >
                            <component
                                :is="stageIcons[step.stage]"
                                class="size-3.5"
                            />
                        </span>
                        <p
                            class="text-xs font-medium tracking-wide text-brand uppercase"
                        >
                            {{ t.stage[step.stage] }}
                        </p>
                        <h3 class="font-semibold">{{ step.title }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ step.detail }}
                        </p>
                        <div class="mt-2">
                            <SourceLink
                                :url="step.source_url"
                                :sources="brief.sources"
                            />
                        </div>
                    </li>
                </ol>
            </section>

            <section>
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
                    <AlertTriangle class="size-5 text-destructive" />{{
                        t.watchOut
                    }}
                </h2>
                <div class="grid grid-cols-1 gap-3">
                    <div
                        v-for="item in c.watch_out"
                        :key="item.title"
                        class="rounded-xl border border-destructive/30 bg-destructive/5 p-4"
                    >
                        <h3 class="font-semibold">{{ item.title }}</h3>
                        <p class="mt-1 text-sm">{{ item.detail }}</p>
                        <div class="mt-2">
                            <SourceLink
                                :url="item.source_url"
                                :sources="brief.sources"
                            />
                        </div>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="mb-4 flex items-center gap-2 text-xl font-semibold">
                    <MessageCircleQuestion class="size-5 text-brand" />{{
                        t.questions
                    }}
                </h2>
                <ul class="grid grid-cols-1 gap-2">
                    <li
                        v-for="question in c.suggested_questions"
                        :key="question"
                        class="rounded-lg border bg-card px-4 py-3 text-sm"
                    >
                        {{ question }}
                    </li>
                </ul>
            </section>

            <section>
                <h2
                    class="mb-3 text-sm font-semibold text-muted-foreground uppercase"
                >
                    {{ t.sources }}
                </h2>
                <ul class="grid grid-cols-1 gap-1 text-sm">
                    <li v-for="source in brief.sources" :key="source.url">
                        <a
                            :href="source.url"
                            target="_blank"
                            rel="noopener"
                            class="group flex min-w-0 items-center gap-2 py-1.5"
                            :title="source.url"
                        >
                            <BadgeCheck
                                v-if="source.official"
                                class="size-4 shrink-0 text-brand"
                            />
                            <ExternalLink
                                v-else
                                class="size-4 shrink-0 text-muted-foreground"
                            />
                            <span dir="ltr" class="min-w-0 truncate">
                                <span
                                    class="font-medium group-hover:underline"
                                    >{{ source.host }}</span
                                >
                                <span class="text-muted-foreground">{{
                                    sourcePath(source.url)
                                }}</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </section>
        </article>
    </div>
</template>
