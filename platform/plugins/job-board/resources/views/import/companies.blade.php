@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row justify-content-center bulk-import">
        <div class="col-xxl-6 col-xl-8 col-lg-10 col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-import"></i> {{ trans('plugins/job-board::import.company.name') }}
                </div>
                <div class="card-body">
                    <form action="{{ route('import-companies.store') }}" method="post" id="bulk-import-form">
                        <div class="mb-3">
                            <label for="file" class="form-label required">{{ trans('plugins/job-board::import.choose_file') }}</label>
                            <input type="file" name="file" id="file" class="form-control" required>
                            <label class="d-block mt-1 help-block" for="input-group-file">
                                {{ trans('plugins/job-board::import.choose_file_description', ['types' =>  implode(', ', config('plugins.job-board.general.bulk-import.mimes', []))])}}
                            </label>
                        </div>
                        <div class="mb-3 text-center p-2 border bg-light">
                            <a
                                class="download-template"
                                data-url="{{ route('import-companies.download-template') }}"
                                data-extension="csv"
                                data-filename="template_companies_import.csv"
                                data-downloading="<i class='fas fa-spinner fa-spin'></i> {{ trans('plugins/job-board::import.downloading') }}"
                            >
                                <i class="fas fa-file-csv"></i>
                                {{ trans('plugins/job-board::import.download_csv_file') }}
                            </a>
                            &nbsp; | &nbsp;
                            <a
                                class="download-template"
                                data-url="{{ route('import-companies.download-template') }}"
                                data-extension="xlsx"
                                data-filename="template_companies_import.xlsx"
                                data-downloading="<i class='fas fa-spinner fa-spin'></i> {{ trans('plugins/job-board::import.downloading') }}"
                            >
                                <i class="fas fa-file-excel"></i>
                                {{ trans('plugins/job-board::import.download_excel_file') }}
                            </a>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info">{{ trans('plugins/job-board::import.company.name') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="alert my-3" style="display: none"></div>
            <div class="widget meta-boxes mt-4" id="failures-list" style="display: none">
                <div class="widget-title">
                    <span class="text-danger">{{ trans('plugins/job-board::import.failures') }}</span>
                </div>
                <div class="widget-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th scope="col">#{{ trans('plugins/job-board::import.row') }}</th>
                            <th scope="col">{{ trans('plugins/job-board::import.attribute') }}</th>
                            <th scope="col">{{ trans('plugins/job-board::import.errors') }}</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="container mt-4">
            <div class="widget meta-boxes mt-4">
                <div class="widget-title">
                    <h4 class="text-info">{{ trans('plugins/job-board::import.template') }}</h4>
                </div>
                <div class="widget-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                            <tr>
                                @foreach($headings as $heading)
                                    <th>{{ $heading }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($companies as $company)
                                <tr>
                                    @foreach($headings as $key => $value)
                                        <td>{{ Arr::get($company, $key) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="widget meta-boxes mt-4">
                <div class="widget-title">
                    <h4 class="text-info">{{ trans('plugins/job-board::import.rules') }}</h4>
                </div>
                <div class="widget-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="row">{{ trans('plugins/job-board::import.column') }}</th>
                            <th scope="col">{{ trans('plugins/job-board::import.rules') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rules as $key => $value)
                            <tr>
                                <th scope="row">{{ Arr::get($headings, $key) }}</th>
                                <td>{{ $value }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
