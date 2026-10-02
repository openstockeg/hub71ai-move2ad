<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { dashboard, home, login } from '@/routes';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="border-b">
            <nav
                class="mx-auto flex h-14 max-w-5xl items-center justify-between px-4"
            >
                <Link :href="home()" class="flex items-center gap-2 font-semibold">
                    <span
                        class="flex size-7 items-center justify-center rounded-md bg-brand text-xs font-bold text-brand-foreground"
                        >M2</span
                    >
                    Move2AD
                </Link>
                <div class="flex items-center gap-4 text-sm">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="text-muted-foreground hover:text-foreground"
                        >My journey</Link
                    >
                    <Link
                        v-else
                        :href="login()"
                        class="text-muted-foreground hover:text-foreground"
                        >Log in</Link
                    >
                </div>
            </nav>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t">
            <div
                class="mx-auto max-w-5xl px-4 py-6 text-xs text-muted-foreground"
            >
                Move2AD is an independent guide, not a government service.
                Answers are grounded in official UAE and Abu Dhabi sources —
                always confirm on the linked page before you act.
            </div>
        </footer>
    </div>
</template>
