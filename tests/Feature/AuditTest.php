<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\City;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\DatabaseTestCase;

class AuditTest extends DatabaseTestCase
{
    public function test_nested_business_transaction_commit_and_rollback(): void
    {
        $actor = User::factory()->create(['role' => 'admin']);
        $subject = City::create(['name' => 'Audit City']);
        $service = app(AuditService::class);
        DB::transaction(fn () => $service->record($actor, 'city.updated', $subject, ['before' => ['is_active' => true], 'after' => ['is_active' => false, 'password' => 'discard', 'nested' => ['token' => 'discard']]], 'local-test'));
        $log = AuditLog::where('subject_type', City::class)->where('subject_id', $subject->id)->firstOrFail();
        $this->assertSame(['is_active' => false], $log->after);
        try {
            DB::transaction(function () use ($service, $actor, $subject) {
                $subject->update(['is_active' => false]);
                $service->record($actor, 'city.failed', $subject);
                throw new \RuntimeException('rollback');
            });
        } catch (\RuntimeException $e) {
            $this->assertSame('rollback', $e->getMessage());
        }
        $this->assertDatabaseMissing('audit_logs', ['subject_id' => $subject->id, 'action' => 'city.failed']);
        $this->assertTrue((bool) $subject->fresh()->is_active);
    }

    public function test_database_rejects_audit_update_and_delete(): void
    {
        $subject = City::create(['name' => 'Immutable']);
        $log = app(AuditService::class)->record(null, 'city.created', $subject);
        foreach (['update', 'delete'] as $operation) {
            try {
                $query = DB::table('audit_logs')->where('id', $log->id);
                $operation === 'update' ? $query->update(['action' => 'tampered']) : $query->delete();
                $this->fail('Audit mutation must fail');
            } catch (QueryException $e) {
                $this->assertStringContainsString('append-only', $e->getMessage());
            }
        }
        $this->assertDatabaseHas('audit_logs', ['id' => $log->id, 'action' => 'city.created']);
    }
}
