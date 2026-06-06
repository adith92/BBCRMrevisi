<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WidgetPreference;

class WidgetController extends Controller
{
    /** Save widget layout (called by CRM_Widget.save()) */
    public function save(Request $request)
    {
        $validated = $request->validate([
            'widgets'           => 'required|array',
            'widgets.*.id'      => 'required|string',
            'widgets.*.visible' => 'required|boolean',
            'widgets.*.order'   => 'required|integer',
        ]);

        WidgetPreference::updateOrCreate(
            ['user_id' => auth()->id()],
            ['widgets' => $validated['widgets']]
        );

        return response()->json(['status' => 'ok']);
    }

    /** Reset to defaults */
    public function reset()
    {
        WidgetPreference::where('user_id', auth()->id())->delete();
        return response()->json(['status' => 'ok', 'widgets' => WidgetPreference::defaultWidgets()]);
    }
}
