<?php

namespace LaravelOpenTracing\Observers;

use LaravelOpenTracing\Facades\Tracing;
use LaravelOpenTracing\Models\TraceableModel;

class ModelObserver {
    private function startTrace(TraceableModel $model, $operation)
    {
        $scope = Tracing::beginTrace(get_class($model));
        $span = $scope->getSpan();
        $span->setTag('operation', $operation);
        $model->addTracingScope($scope);
    }

    private function endTrace(TraceableModel $model)
    {
        $scope = $model->getLatestScope();
        Tracing::endTrace($scope);
    }

    public function creating(TraceableModel $model) {
        $this->startTrace($model, 'create');
    }
    public function created(TraceableModel $model) {
        $this->endTrace($model);
    }

    public function updating(TraceableModel $model) {
        $this->startTrace($model, 'update');
    }
    public function updated(TraceableModel $model) {
        $this->endTrace($model);
    }

    public function deleting(TraceableModel $model) {
        $this->startTrace($model, 'delete');
    }
    public function deleted(TraceableModel $model) {
        $this->endTrace($model);
    }

    public function restoring(TraceableModel $model) {
        $this->startTrace($model, 'restore');
    }
    public function restored(TraceableModel $model) {
        $this->endTrace($model);
    }
}
