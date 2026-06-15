import React from 'react';
import { Trophy } from 'lucide-react';

interface FleetItem {
  rank: number;
  name: string;
  status: 'HIGH PERFORMER' | 'STABLE' | 'NEEDS GROWTH';
  percentage: number;
  colorClass: string; // Tailwind bg color
  badgeClass: string; // Tailwind badge styling
}

const fleetData: FleetItem[] = [
  {
    rank: 1,
    name: 'Golden Bird',
    status: 'HIGH PERFORMER',
    percentage: 75,
    colorClass: 'bg-amber-500',
    badgeClass: 'bg-amber-500/10 text-amber-500 border-amber-500/20'
  },
  {
    rank: 2,
    name: 'Big Bird',
    status: 'STABLE',
    percentage: 67,
    colorClass: 'bg-emerald-500',
    badgeClass: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
  },
  {
    rank: 3,
    name: 'Cititrans',
    status: 'NEEDS GROWTH',
    percentage: 100,
    colorClass: 'bg-blue-500',
    badgeClass: 'bg-blue-500/10 text-blue-400 border-blue-500/20'
  }
];

export default function FleetLeague() {
  return (
    <div className="bg-[#111827] border border-slate-800 rounded-2xl p-6 shadow-xl flex flex-col justify-between h-full text-white">
      {/* Header */}
      <div className="flex items-center justify-between mb-8">
        <div className="flex items-center gap-3">
          <Trophy className="w-6 h-6 text-amber-500" />
          <h2 className="text-sm font-semibold tracking-wider text-slate-200 uppercase">
            FLEET LEAGUE
          </h2>
        </div>
        <button className="text-sm font-medium text-sky-400 hover:text-sky-300 transition-colors">
          Lihat Semua
        </button>
      </div>

      {/* Leaderboard List */}
      <div className="space-y-6">
        {fleetData.map((fleet) => (
          <div key={fleet.rank} className="space-y-2">
            {/* Row Info */}
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-3">
                {/* Rank Number */}
                <div className="bg-slate-800 w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-slate-400 text-sm">
                  {fleet.rank}
                </div>
                {/* Fleet Name */}
                <span className="font-semibold text-slate-200 text-sm">{fleet.name}</span>
              </div>

              {/* Badge & Percentage */}
              <div className="flex items-center gap-4">
                <span className={`px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border ${fleet.badgeClass}`}>
                  {fleet.status}
                </span>
                <span className="font-mono font-bold text-slate-200 text-sm">
                  {fleet.percentage}%
                </span>
              </div>
            </div>

            {/* Thick Progress Bar */}
            <div className="w-full bg-slate-800/60 h-2.5 rounded-full overflow-hidden">
              <div
                className={`h-full rounded-full transition-all duration-500 ${fleet.colorClass}`}
                style={{ width: `${fleet.percentage}%` }}
              />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
