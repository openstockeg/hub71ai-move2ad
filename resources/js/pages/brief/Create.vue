<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    Compass,
    Home,
    Luggage,
    Plane,
    Rocket,
    ShieldAlert,
    Sparkles,
} from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/briefs';
import checks from '@/routes/checks';

// Autofocus only on desktop: on phones it pops the keyboard and suggestion list over the hero.
const isDesktop = window.matchMedia('(min-width: 768px)').matches;

const professions = [
    'Software engineer',
    'Data scientist',
    'AI / ML engineer',
    'Registered nurse',
    'Doctor',
    'Teacher',
    'Civil engineer',
    'Electrical engineer',
    'Accountant',
    'Financial analyst',
    'Marketing manager',
    'Product manager',
    'Architect',
    'Pharmacist',
    'Startup founder',
];

const countries = [
    'Egypt',
    'India',
    'Pakistan',
    'Philippines',
    'Jordan',
    'Lebanon',
    'Morocco',
    'Tunisia',
    'Nigeria',
    'Kenya',
    'United Kingdom',
    'United States',
    'Turkey',
    'Brazil',
];

const families = [
    { value: 'single', label: 'Just me' },
    { value: 'couple', label: 'Couple' },
    { value: 'family', label: 'Family' },
];

const locales = [
    { value: 'en', label: 'English' },
    { value: 'ar', label: 'العربية' },
];

const promises = [
    {
        icon: BadgeCheck,
        text: 'Answers link to official UAE and Abu Dhabi sources',
    },
    { icon: Sparkles, text: 'Personal to your profession, country and family' },
    { icon: ShieldAlert, text: 'Flags scams before they cost you money' },
];

// Same five stages the brief's "Your path" uses.
const journey = [
    {
        icon: Compass,
        stage: 'Explore',
        title: 'Do I fit?',
        text: 'Visa routes that match your profession, rated likely to unlikely.',
    },
    {
        icon: Plane,
        stage: 'Visit',
        title: 'Should I fly in first?',
        text: 'When a job-seeker visa helps, and when it just costs you.',
    },
    {
        icon: Luggage,
        stage: 'Move',
        title: 'Is this offer real?',
        text: 'Paste any offer, get every red flag with the law behind it.',
        href: checks.create(),
    },
    {
        icon: Home,
        stage: 'Settle',
        title: 'What will life cost?',
        text: 'Rent by area, upfront deposits and how to rent safely.',
    },
    {
        icon: Rocket,
        stage: 'Build',
        title: 'How do I stay long-term?',
        text: 'Golden and Green Visa thresholds once you are here.',
    },
];
</script>

<template>
    <Head title="Your move to Abu Dhabi, answered" />

    <div>
        <section class="bg-sand">
            <!-- Mobile order: intro → form → promises. Desktop: intro + promises left, form right. -->
            <div
                class="mx-auto grid max-w-5xl grid-cols-1 gap-6 px-4 py-6 md:grid-cols-[1fr_420px] md:gap-x-10 md:gap-y-8 md:py-20"
            >
                <div class="flex flex-col justify-end">
                    <h1
                        class="text-4xl leading-[1.05] font-semibold tracking-[-0.03em] text-balance md:text-6xl"
                    >
                        Can I move to Abu Dhabi?
                    </h1>
                    <p
                        class="mt-4 max-w-lg text-base text-pretty text-muted-foreground md:mt-6 md:text-lg"
                    >
                        Get the trusted answer for you. Tell us who you are and
                        in under a minute you get a personal brief: which visa
                        fits, what life costs, the exact steps, and the scams to
                        avoid. Every claim links to its official source.
                    </p>
                </div>

                <Form
                    v-bind="store.form()"
                    v-slot="{ errors, processing }"
                    class="grid gap-5 rounded-xl border bg-card p-5 shadow-sm md:col-start-2 md:row-span-2 md:row-start-1 md:self-center md:p-6"
                >
                    <h2 class="text-lg font-semibold">
                        Get your Abu Dhabi brief
                    </h2>

                    <div class="grid gap-2">
                        <Label for="profession">Your profession</Label>
                        <Input
                            id="profession"
                            name="profession"
                            list="professions"
                            autocomplete="off"
                            required
                            v-focus="isDesktop"
                            placeholder="e.g. Software engineer, nurse"
                        />
                        <datalist id="professions">
                            <option
                                v-for="p in professions"
                                :key="p"
                                :value="p"
                            />
                        </datalist>
                        <InputError :message="errors.profession" />
                    </div>

                    <div class="grid grid-cols-[1fr_110px] gap-3">
                        <div class="grid gap-2">
                            <Label for="country">Where you live now</Label>
                            <Input
                                id="country"
                                name="country"
                                list="countries"
                                autocomplete="off"
                                required
                                placeholder="e.g. Egypt"
                            />
                            <datalist id="countries">
                                <option
                                    v-for="c in countries"
                                    :key="c"
                                    :value="c"
                                />
                            </datalist>
                            <InputError :message="errors.country" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="experience_years">Years exp.</Label>
                            <Input
                                id="experience_years"
                                name="experience_years"
                                type="number"
                                min="0"
                                max="50"
                                required
                                default-value="5"
                            />
                            <InputError :message="errors.experience_years" />
                        </div>
                    </div>

                    <fieldset class="grid gap-2">
                        <legend class="mb-2 text-sm font-medium">
                            Who is moving?
                        </legend>
                        <div class="grid grid-cols-3 gap-2">
                            <label
                                v-for="(option, i) in families"
                                :key="option.value"
                                class="cursor-pointer rounded-md border px-2 py-2 text-center text-sm has-checked:border-brand has-checked:bg-brand/10 has-checked:font-medium"
                            >
                                <input
                                    type="radio"
                                    name="family"
                                    :value="option.value"
                                    :checked="i === 0"
                                    class="sr-only"
                                />
                                {{ option.label }}
                            </label>
                        </div>
                        <InputError :message="errors.family" />
                    </fieldset>

                    <fieldset class="grid gap-2">
                        <legend class="mb-2 text-sm font-medium">
                            Brief language
                        </legend>
                        <div class="grid grid-cols-2 gap-2">
                            <label
                                v-for="(option, i) in locales"
                                :key="option.value"
                                class="cursor-pointer rounded-md border px-2 py-2 text-center text-sm has-checked:border-brand has-checked:bg-brand/10 has-checked:font-medium"
                            >
                                <input
                                    type="radio"
                                    name="locale"
                                    :value="option.value"
                                    :checked="i === 0"
                                    class="sr-only"
                                />
                                {{ option.label }}
                            </label>
                        </div>
                    </fieldset>

                    <Button
                        type="submit"
                        size="lg"
                        class="bg-brand text-brand-foreground hover:bg-brand/90"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" />
                        Build my brief
                    </Button>
                    <p class="text-center text-xs text-muted-foreground">
                        Free, no account needed
                    </p>
                </Form>

                <ul class="grid gap-3 self-start">
                    <li
                        v-for="promise in promises"
                        :key="promise.text"
                        class="flex items-center gap-3 text-sm"
                    >
                        <component
                            :is="promise.icon"
                            class="size-5 shrink-0 text-brand"
                        />
                        {{ promise.text }}
                    </li>
                    <li>
                        <Link
                            :href="checks.create()"
                            class="mt-1 inline-flex items-center gap-2 rounded-lg border border-destructive/30 bg-card px-3 py-2 text-sm font-medium hover:border-destructive/60"
                        >
                            <ShieldAlert class="size-4 text-destructive" />
                            Already have a job offer? Check it for scams
                        </Link>
                    </li>
                </ul>
            </div>
        </section>

        <section class="mx-auto max-w-5xl px-4 py-12 md:py-16">
            <h2
                class="max-w-xl text-2xl font-semibold tracking-tight text-balance md:text-3xl"
            >
                One brief covers the whole move, from first question to staying
                for good
            </h2>
            <!-- A connected path: vertical on phones, horizontal on desktop. -->
            <ol class="mt-8 grid gap-8 md:mt-10 lg:grid-cols-5 lg:gap-6">
                <li
                    v-for="step in journey"
                    :key="step.stage"
                    class="relative ps-12 before:absolute before:start-4 before:top-10 before:-bottom-6 before:w-0.5 before:bg-brand/25 last:before:hidden lg:ps-0 lg:pt-14 lg:before:start-11 lg:before:-end-4 lg:before:top-4 lg:before:bottom-auto lg:before:h-0.5 lg:before:w-auto"
                >
                    <span
                        class="absolute start-0 top-0 flex size-8 items-center justify-center rounded-full bg-brand text-brand-foreground"
                    >
                        <component :is="step.icon" class="size-4" />
                    </span>
                    <p class="text-sm font-medium text-brand">
                        {{ step.stage }}
                    </p>
                    <p class="mt-1 font-semibold">{{ step.title }}</p>
                    <p class="mt-1 text-sm text-pretty text-muted-foreground">
                        {{ step.text }}
                    </p>
                    <Link
                        v-if="step.href"
                        :href="step.href"
                        class="mt-2 inline-block text-sm font-medium text-brand underline underline-offset-4 hover:no-underline"
                        >Check a job offer</Link
                    >
                </li>
            </ol>
        </section>
    </div>
</template>
