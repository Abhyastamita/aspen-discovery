{strip}
	<div id="main-content" class="col-sm-12">
		<h1>{translate text="ILS Usage Dashboard" isAdminFacing=true}</h1>
		{include file="Admin/selectInterfaceForm.tpl"}
		{foreach from=$profiles item=profileName key=profileId}
			<h1>{translate text="Selected Profile" isAdminFacing=true} - {$profileName}</h1>
			<div class="row">
				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="User Logins" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=userLogins&instance={$selectedInstance}" title="{translate text="Show User Logins Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisMonth.$profileId.totalUsers}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersLastMonth.$profileId.totalUsers}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisYear.$profileId.totalUsers}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersAllTime.$profileId.totalUsers}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="Self Registrations" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=selfRegistrations&instance={$selectedInstance}" title="{translate text="Show Self Registration Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisMonth.$profileId.numSelfRegistrations}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersLastMonth.$profileId.numSelfRegistrations}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisYear.$profileId.numSelfRegistrations}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersAllTime.$profileId.numSelfRegistrations}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="Users who placed at least one hold" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=usersWithHolds&instance={$selectedInstance}" title="{translate text="Show Users Who Placed At Least One Hold Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisMonth.$profileId.usersWithHolds}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersLastMonth.$profileId.usersWithHolds}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisYear.$profileId.usersWithHolds}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersAllTime.$profileId.usersWithHolds}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="Records Held" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=recordsHeld&instance={$selectedInstance}" title="{translate text="Show Records Held Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$profileId.numRecordsUsed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$profileId.numRecordsUsed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$profileId.numRecordsUsed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$profileId.numRecordsUsed}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="Total Holds" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=totalHolds&instance={$selectedInstance}" title="{translate text="Show Total Holds Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$profileId.totalHolds}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$profileId.totalHolds}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$profileId.totalHolds}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$profileId.totalHolds}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="Users who downloaded at least one PDF" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=usersWithPdfDownloads&instance={$selectedInstance}" title="{translate text="Show Who Downloaded At Least One PDF Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisMonth.$profileId.usersWithPdfDownloads}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersLastMonth.$profileId.usersWithPdfDownloads}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisYear.$profileId.usersWithPdfDownloads}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersAllTime.$profileId.usersWithPdfDownloads}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="Users who viewed at least one PDF" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=usersWithPdfViews&instance={$selectedInstance}" title="{translate text="Show Users Who Viewed At Least One PDF Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisMonth.$profileId.usersWithPdfViews}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersLastMonth.$profileId.usersWithPdfViews}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisYear.$profileId.usersWithPdfViews}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersAllTime.$profileId.usersWithPdfViews}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="PDFs Downloaded" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=pdfsDownloaded&instance={$selectedInstance}" title="{translate text="Show PDFs Downloaded Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$profileId.numPDFsDownloaded}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$profileId.numPDFsDownloaded}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$profileId.numPDFsDownloaded}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$profileId.numPDFsDownloaded}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="PDFs Viewed" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=pdfsViewed&instance={$selectedInstance}" title="{translate text="Show PDFs Viewed Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$profileId.numPDFsViewed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$profileId.numPDFsViewed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$profileId.numPDFsViewed}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$profileId.numPDFsViewed}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="Users who downloaded at least one Supplemental File" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=usersWithSupplementalFileDownloads&instance={$selectedInstance}" title="{translate text="Show Users Who Downloaded At Least One Supplemental File Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisMonth.$profileId.usersWithSupplementalFileDownloads}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersLastMonth.$profileId.usersWithSupplementalFileDownloads}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersThisYear.$profileId.usersWithSupplementalFileDownloads}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeUsersAllTime.$profileId.usersWithSupplementalFileDownloads}</div>
						</div>
					</div>
				</div>

				<div class="dashboardCategory col-sm-6">
					<div class="row">
						<div class="col-sm-10 col-sm-offset-1">
							<h2 class="dashboardCategoryLabel">{translate text="Supplemental Files Downloaded" isAdminFacing=true} <a href="/ILS/UsageGraphs?stat=supplementalFilesDownloaded&instance={$selectedInstance}" title="{translate text="Show Supplemental Files Downloaded Graph" inAttribute="true"}"><i class="fas fa-chart-line"></i></a></h2>
						</div>
					</div>
					<div class="row">
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisMonth.$profileId.numSupplementalFileDownloadCount}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="Last Month" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsLastMonth.$profileId.numSupplementalFileDownloadCount}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="This Year" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsThisYear.$profileId.numSupplementalFileDownloadCount}</div>
						</div>
						<div class="col-tn-6">
							<div class="dashboardLabel">{translate text="All Time" isAdminFacing=true}</div>
							<div class="dashboardValue">{$activeRecordsAllTime.$profileId.numSupplementalFileDownloadCount}</div>
						</div>
					</div>
				</div>
			</div>
		{/foreach}
	</div>
{/strip}