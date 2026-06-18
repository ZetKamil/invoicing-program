<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('generate_ubl')
                ->label('Genereer Peppol UBL')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (\App\Models\Invoice $record, \App\Actions\Invoices\GenerateUblXmlAction $generateAction) {
                    try {
                        $path = $generateAction->execute($record);
                        $record->update(['ubl_xml_path' => $path]);
                        \Filament\Notifications\Notification::make()->title('Peppol UBL gegenereerd!')->success()->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()->title('Fout bij genereren UBL: ' . $e->getMessage())->danger()->send();
                    }
                }),
            \Filament\Actions\Action::make('download_ubl')
                ->label('Download Peppol UBL')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->visible(fn (\App\Models\Invoice $record) => !empty($record->ubl_xml_path))
                ->action(function (\App\Models\Invoice $record) {
                    return response()->streamDownload(function () use ($record) {
                        echo \Illuminate\Support\Facades\Storage::get($record->ubl_xml_path);
                    }, $record->invoice_number . '-peppol.xml', [
                        'Content-Type' => 'application/xml',
                        'Content-Disposition' => 'attachment; filename="'.$record->invoice_number.'-peppol.xml"',
                    ]);
                }),
            DeleteAction::make(),
        ];
    }
}
