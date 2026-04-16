<?php

namespace Botble\Team\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Team\Forms\TeamForm;
use Botble\Team\Http\Requests\TeamRequest;
use Botble\Team\Repositories\Interfaces\TeamInterface;
use Botble\Team\Tables\TeamTable;
use Exception;
use Illuminate\Http\Request;

class TeamController extends BaseController
{
    public function __construct(protected TeamInterface $teamRepository)
    {
    }

    public function index(TeamTable $table)
    {
        PageTitle::setTitle(trans('plugins/team::team.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        PageTitle::setTitle(trans('plugins/team::team.create'));

        return $formBuilder->create(TeamForm::class)->renderForm();
    }

    public function store(TeamRequest $request, BaseHttpResponse $response)
    {
        $data = $request->input();
        $data['socials'] = json_encode($request->input('socials'));

        $team = $this->teamRepository->createOrUpdate($data);

        event(new CreatedContentEvent(TEAM_MODULE_SCREEN_NAME, $request, $team));

        return $response
            ->setPreviousUrl(route('team.index'))
            ->setNextUrl(route('team.edit', $team->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(int|string $id, FormBuilder $formBuilder, Request $request)
    {
        $team = $this->teamRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $team));

        PageTitle::setTitle(trans('core/base::forms.edit_item', ['name' => $team->name]));

        return $formBuilder->create(TeamForm::class, ['model' => $team])->renderForm();
    }

    public function update(int|string $id, TeamRequest $request, BaseHttpResponse $response)
    {
        $team = $this->teamRepository->findOrFail($id);

        $team->fill($request->input());

        $team = $this->teamRepository->createOrUpdate($team);

        event(new UpdatedContentEvent(TEAM_MODULE_SCREEN_NAME, $request, $team));

        return $response
            ->setPreviousUrl(route('team.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(int|string $id, Request $request, BaseHttpResponse $response)
    {
        try {
            $team = $this->teamRepository->findOrFail($id);

            $this->teamRepository->delete($team);

            event(new DeletedContentEvent(TEAM_MODULE_SCREEN_NAME, $request, $team));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception $exception) {
            return $response
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $team = $this->teamRepository->findOrFail($id);
            $this->teamRepository->delete($team);
            event(new DeletedContentEvent(TEAM_MODULE_SCREEN_NAME, $request, $team));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
