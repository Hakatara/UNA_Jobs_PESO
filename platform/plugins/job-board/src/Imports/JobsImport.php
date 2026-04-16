<?php

namespace Botble\JobBoard\Imports;

use Botble\Base\Events\CreatedContentEvent;
use Botble\JobBoard\Contracts\OnSuccesses;
use Botble\JobBoard\Contracts\Typeable;
use Botble\JobBoard\Contracts\Validatable;
use Botble\JobBoard\Models\Job;
use Botble\JobBoard\Repositories\Interfaces\CareerLevelInterface;
use Botble\JobBoard\Repositories\Interfaces\CategoryInterface;
use Botble\JobBoard\Repositories\Interfaces\CompanyInterface;
use Botble\JobBoard\Repositories\Interfaces\CurrencyInterface;
use Botble\JobBoard\Repositories\Interfaces\DegreeLevelInterface;
use Botble\JobBoard\Repositories\Interfaces\FunctionalAreaInterface;
use Botble\JobBoard\Repositories\Interfaces\JobExperienceInterface;
use Botble\JobBoard\Repositories\Interfaces\JobInterface;
use Botble\JobBoard\Repositories\Interfaces\JobShiftInterface;
use Botble\JobBoard\Repositories\Interfaces\JobSkillInterface;
use Botble\JobBoard\Repositories\Interfaces\JobTypeInterface;
use Botble\JobBoard\Repositories\Interfaces\TagInterface;
use Botble\Location\Repositories\Interfaces\CityInterface;
use Botble\Location\Repositories\Interfaces\CountryInterface;
use Botble\Location\Repositories\Interfaces\StateInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithValidation;

class JobsImport implements
    ToModel,
    WithValidation,
    WithChunkReading,
    WithHeadingRow,
    WithMapping
{
    use Importable;
    use SkipsFailures;
    use SkipsErrors;
    use Validatable;
    use OnSuccesses;
    use Typeable;

    public function __construct(
        protected Request $request,
        protected JobInterface $jobRepository,
        protected CountryInterface $countryRepository,
        protected StateInterface $stateRepository,
        protected CityInterface $cityRepository,
        protected CurrencyInterface $currencyRepository,
        protected CompanyInterface $companyRepository,
        protected CareerLevelInterface $careerLevelRepository,
        protected DegreeLevelInterface $degreeLevelRepository,
        protected JobShiftInterface $jobShiftRepository,
        protected JobExperienceInterface $jobExperienceRepository,
        protected FunctionalAreaInterface $functionalAreaRepository,
        protected JobSkillInterface $jobSkillRepository,
        protected CategoryInterface $categoryRepository,
        protected JobTypeInterface $jobTypeRepository,
        protected TagInterface $tagRepository
    ) {
    }

    public function model(array $row): Model
    {
        $job = new Job();
        $job->forceFill(Arr::except($row, ['skills', 'categories', 'types', 'tags']));
        $job->save();

        $job->skills()->sync(Arr::get($row, 'skills', []));
        $job->categories()->sync(Arr::get($row, 'categories', []));
        $job->jobTypes()->sync(Arr::get($row, 'types', []));
        $job->tags()->sync(Arr::get($row, 'tags', []));

        $job->author()->associate(auth()->user());

        $this->request->merge([
            'slug' => Str::slug($job->name),
            'is_slug_editable' => true,
        ]);

        event(new CreatedContentEvent(JOB_MODULE_SCREEN_NAME, $this->request, $job));

        $this->onSuccess($job);

        return $job;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function map($row): array
    {
        $job = [
            'name' => Arr::get($row, 'name'),
            'description' => Arr::get($row, 'description'),
            'content' => Arr::get($row, 'content'),
            'apply_url' => Arr::get($row, 'apply_url'),
            'address' => Arr::get($row, 'address'),
            'is_freelance' => $this->yesNoToBoolean(Arr::get($row, 'is_freelance', false)),
            'salary_from' => Arr::get($row, 'salary_from'),
            'salary_to' => Arr::get($row, 'salary_to'),
            'salary_range' => Arr::get($row, 'salary_range'),
            'hide_salary' => $this->yesNoToBoolean(Arr::get($row, 'hide_salary', false)),
            'number_of_positions' => Arr::get($row, 'number_of_positions', 0),
            'expire_date' => Arr::get($row, 'expire_date'),
            'hide_company' => $this->yesNoToBoolean(Arr::get($row, 'hide_company', false)),
            'latitude' => Arr::get($row, 'latitude'),
            'longitude' => Arr::get($row, 'longitude'),
            'is_featured' => $this->yesNoToBoolean(Arr::get($row, 'is_featured', false)),
            'auto_renew' => $this->yesNoToBoolean(Arr::get($row, 'auto_renew', false)),
            'never_expired' => ! Arr::get($row, 'expire_date') || $this->yesNoToBoolean(Arr::get($row, 'never_expired')),
            'employer_colleagues' => $this->stringToArray(Arr::get($row, 'employer_colleagues')),
            'start_date' => Arr::get($row, 'start_date'),
            'application_closing_date' => Arr::get($row, 'application_closing_date'),
            'status' => Arr::get($row, 'status'),
            'moderation_status' => Arr::get($row, 'moderation_status'),
        ];

        if (JobBoardHelper::isZipCodeEnabled()) {
            $job = array_merge($job, [
                'zip_code' => Arr::get($row, 'zip_code'),
            ]);
        }

        return $this->mapRelationships($row, $job);
    }

    public function mapRelationships(mixed $row, array $job): array
    {
        $job['country_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'country'), $this->countryRepository));
        $job['state_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'state'), $this->stateRepository));
        $job['city_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'city'), $this->cityRepository));
        $job['currency_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'currency'), $this->currencyRepository, 'title'));
        $job['company_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'company_name'), $this->companyRepository));
        $job['career_level_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'career_level'), $this->careerLevelRepository));
        $job['degree_level_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'degree_level'), $this->degreeLevelRepository));
        $job['job_shift_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'job_shift'), $this->jobShiftRepository));
        $job['job_experience_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'job_experience'), $this->jobExperienceRepository));
        $job['functional_area_id'] = Arr::first($this->stringToModelIds(Arr::get($row, 'functional_area'), $this->functionalAreaRepository));

        $job['skills'] = $this->stringToModelIds(Arr::get($row, 'skills'), $this->jobSkillRepository);
        $job['categories'] = $this->stringToModelIds(Arr::get($row, 'categories'), $this->categoryRepository);
        $job['types'] = $this->stringToModelIds(Arr::get($row, 'types'), $this->jobTypeRepository);
        $job['tags'] = $this->stringToModelIds(Arr::get($row, 'tags'), $this->tagRepository);

        return $job;
    }
}
