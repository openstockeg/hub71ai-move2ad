export type Stage = 'explore' | 'visit' | 'move' | 'settle' | 'build';

export type BriefContent = {
    headline: string;
    summary: string;
    fit: {
        level: 'strong' | 'good' | 'possible' | 'challenging';
        reason: string;
        source_url: string | null;
    };
    visa_paths: {
        name: string;
        duration: string;
        requirements: string;
        likelihood: 'likely' | 'possible' | 'unlikely';
        why: string;
        source_url: string | null;
    }[];
    money: {
        salary_range: string | null;
        rent_1br: string;
        upfront_costs: string;
        note: string;
        source_url: string | null;
    };
    steps: {
        stage: Stage;
        title: string;
        detail: string;
        source_url: string | null;
    }[];
    watch_out: { title: string; detail: string; source_url: string | null }[];
    suggested_questions: string[];
};

export type Source = { url: string; host: string; official: boolean };

export type Brief = {
    id: string;
    country: string;
    profession: string;
    experience_years: number;
    family: 'single' | 'couple' | 'family';
    locale: 'en' | 'ar';
    status: 'pending' | 'generating' | 'ready' | 'failed';
    content: BriefContent | null;
    sources: Source[];
    created_at: string;
};
